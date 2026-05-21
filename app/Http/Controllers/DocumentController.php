<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Reservation;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class DocumentController extends Controller
{
    /**
     * Download a document
     */
    public function download(Document $document)
    {
        // Check if user has access to this document
        $this->authorizeAccess($document);

        // Block clients from downloading the payment plan PDF until they accept the budget.
        // Admins can always download to review.
        if ($document->document_type === 'payment_plan' && Auth::user()?->role !== 'admin') {
            $reservation = $document->reservation;
            $accepted = $reservation && ($reservation->budget_status === 'approved'
                || in_array($reservation->status, ['contract_signed', 'signed']));
            if (! $accepted) {
                abort(403, 'Tenés que marcar como conforme el plan de pagos antes de descargarlo.');
            }
        }

        // Resolve the absolute path. The file can live in:
        //   - storage/app/public/...   (Storage disk "public")  → e.g. onboarding/{user}/id_front.jpg
        //   - public/documents/...     (legacy upload path)     → e.g. documents/id_RES-XYZ_123.jpg
        //   - public/...               (other absolute paths under web root)
        $resolveAbsolutePath = function (?string $rel): ?string {
            if (! $rel || $rel === 'pending') return null;
            $candidates = [
                storage_path('app/public/' . ltrim($rel, '/')),
                public_path(ltrim($rel, '/')),
            ];
            foreach ($candidates as $c) {
                if (is_file($c)) return $c;
            }
            return null;
        };

        // For auto-generated docs (payment_plan / purchase_promise / contract), always
        // regenerate from the current reservation data so the file reflects the latest plan.
        // We skip regeneration once a doc is signed — the signed copy is the authoritative file.
        $regenerableTypes = ['payment_plan', 'purchase_promise', 'contract'];
        if (in_array($document->document_type, $regenerableTypes) && ! $document->isSigned() && ! $document->isApproved()) {
            $reservation = $document->reservation;
            if ($reservation) {
                // Template paths used by AdminController. The fallback DocumentBuilder is
                // ONLY used when the user's .docx template is missing — we never overwrite
                // a template-based file with a programmatic one (keeps the user's branding).
                $templates = [
                    'payment_plan'     => storage_path('app/templates/plan_de_pagos.docx'),
                    'purchase_promise' => [
                        storage_path('app/templates/promesa_compraventa.docx'),
                        storage_path('app/templates/promesa_compravente.docx'),
                    ],
                    'contract'         => storage_path('app/templates/contract_template.docx'),
                ];
                $templateExists = (function () use ($templates, $document) {
                    $paths = (array) ($templates[$document->document_type] ?? []);
                    foreach ($paths as $p) if (is_file($p)) return true;
                    return false;
                })();

                // 1st attempt: template-based generation (preserves the user's design)
                try {
                    $adminController = new \App\Http\Controllers\AdminController();
                    if ($document->document_type === 'payment_plan') {
                        $adminController->generatePaymentPlan($reservation);
                    } elseif ($document->document_type === 'purchase_promise') {
                        $adminController->generatePurchasePromise($reservation);
                    } elseif ($document->document_type === 'contract') {
                        $adminController->generateContract($reservation);
                    }
                    $document->refresh();
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Template-based generation failed: '.$e->getMessage());
                }

                // Fallback: ONLY when the template is missing AND no file was produced.
                // We never overwrite a template-based file — that would lose the user's design.
                if (! $templateExists && ! $resolveAbsolutePath($document->file_path)) {
                    try {
                        if ($document->document_type === 'payment_plan') {
                            \App\Services\DocumentBuilder::buildPaymentPlan($reservation);
                        } elseif ($document->document_type === 'purchase_promise') {
                            \App\Services\DocumentBuilder::buildPurchasePromise($reservation);
                        }
                        $document->refresh();
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('Fallback DocumentBuilder failed: '.$e->getMessage());
                    }
                }
            }
        }

        $absolute = $resolveAbsolutePath($document->file_path);
        if (! $absolute) {
            abort(404, 'Documento no encontrado');
        }

        return Response::download($absolute, $document->filename ?: basename($absolute));
    }
    
    /**
     * Sign a document
     */
    public function sign(Request $request, Document $document)
    {
        // Check if user has access to this document
        $this->authorizeAccess($document);

        // Gate: the purchase_promise (and any later contract) can only be signed AFTER
        // the payment plan is signed AND the client explicitly accepted the contract.
        if (in_array($document->document_type, ['purchase_promise', 'contract'])) {
            $reservation = $document->reservation;
            $planDoc = $reservation?->documents->firstWhere('document_type', 'payment_plan');
            if (! $planDoc || ! in_array($planDoc->status, ['signed', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Primero tenés que firmar el plan de pagos.',
                ], 400);
            }
            if (empty(data_get($document->metadata, 'accepted_at'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aceptá el contrato antes de firmarlo.',
                ], 400);
            }
        }

        // Auto-generate the file from the current reservation data if it's still pending
        // (e.g. payment_plan/purchase_promise/contract that haven't been built yet).
        $generatableTypes = ['payment_plan', 'purchase_promise', 'contract'];
        if ($document->isPending() && in_array($document->document_type, $generatableTypes)) {
            try {
                $reservation = $document->reservation;
                if ($reservation) {
                    $adminController = new \App\Http\Controllers\AdminController();
                    if ($document->document_type === 'payment_plan') {
                        $adminController->generatePaymentPlan($reservation);
                    } elseif ($document->document_type === 'purchase_promise') {
                        $adminController->generatePurchasePromise($reservation);
                    } elseif ($document->document_type === 'contract') {
                        $adminController->generateContract($reservation);
                    }
                    $document->refresh();
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Could not auto-generate on sign: ' . $e->getMessage());
            }
        }

        // Only allow signing if document is generated
        if (!$document->isGenerated()) {
            return response()->json([
                'success' => false,
                'message' => 'El documento debe estar generado antes de poder firmarlo'
            ], 400);
        }

        // If SignNow is configured, send the email invite and tell the client to check inbox.
        // We do NOT mark the doc as signed locally — that happens via the SignNow webhook
        // (or the admin's "Sincronizar firma" button) once the recipient actually signs.
        if (\App\Services\SignNowService::isConfigured()) {
            try {
                $signerEmail = $document->reservation?->email ?? Auth::user()?->email;
                $signerName  = trim(($document->reservation?->first_name ?? '').' '.($document->reservation?->last_name ?? ''))
                    ?: (Auth::user()?->name ?? 'Cliente');

                $result = \App\Services\SignNowService::sendForSignature($document, $signerEmail, $signerName);

                return response()->json([
                    'success'      => true,
                    'email_sent'   => true,
                    'signer_email' => $result['signer_email'] ?? $signerEmail,
                    'message'      => 'Te enviamos un correo a '.$signerEmail.' con el link para firmar. Revisalo en tu bandeja de entrada (también en spam).',
                    'provider'     => 'signnow',
                    'status'       => $document->fresh()->status,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('SignNow signing failed, falling back to local sign: '.$e->getMessage());
                // fall through to local signing
            }
        }

        // Local fallback: mark the doc as signed server-side
        try {
            DocumentService::signDocument($document, Auth::id(), $request->input('notes'));
            $document->refresh();

            return response()->json([
                'success'  => true,
                'message'  => 'Documento firmado exitosamente',
                'provider' => 'local',
                'status'   => $document->status,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al firmar el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Poll SignNow for the current status of a document and, if it's signed,
     * pull the signed file into our storage. Used when webhooks aren't available
     * (e.g. local dev) and called automatically when the client returns from SignNow.
     */
    public function signnowSync(Document $document)
    {
        $this->authorizeAccess($document);

        if (! \App\Services\SignNowService::isConfigured()) {
            return response()->json(['success' => false, 'message' => 'SignNow no está configurado.'], 400);
        }
        if ($document->isSigned()) {
            return response()->json(['success' => true, 'status' => 'signed', 'already' => true]);
        }
        $signnowDocId = data_get($document->metadata, 'signnow.document_id');
        if (! $signnowDocId) {
            return response()->json(['success' => false, 'message' => 'Este documento no fue enviado a SignNow todavía.'], 400);
        }

        $path = \App\Services\SignNowService::downloadSignedFile($document);
        $document->refresh();

        return response()->json([
            'success' => true,
            'status'  => $document->status,
            'signed'  => $document->isSigned(),
            'path'    => $path,
        ]);
    }

    /**
     * Webhook endpoint SignNow calls when a document is signed.
     * Pulls the signed file back into our storage and marks the doc as signed.
     */
    public function signnowWebhook(Request $request)
    {
        $secret = config('signnow.webhook_secret');
        if ($secret) {
            $signature = $request->header('X-Signnow-Signature');
            $expected = hash_hmac('sha256', $request->getContent(), $secret);
            if (! $signature || ! hash_equals($expected, $signature)) {
                abort(401, 'Invalid SignNow signature');
            }
        }

        $signnowDocId = $request->input('document_id') ?? $request->input('content.document_id');
        if (! $signnowDocId) return response()->json(['ok' => false], 400);

        $document = Document::where('metadata->signnow.document_id', $signnowDocId)->first();
        if (! $document) return response()->json(['ok' => true, 'note' => 'no matching document']);

        \App\Services\SignNowService::downloadSignedFile($document);

        return response()->json(['ok' => true]);
    }
    
    /**
     * Approve a document
     */
    public function approve(Request $request, Document $document)
    {
        // Check if user has access to this document
        $this->authorizeAccess($document);

        $isKycReview = in_array($document->document_type, ['id_front', 'id_back', 'kyc']) && $document->isPending();
        $isAdmin = Auth::user()?->role === 'admin';

        // Signed contracts go through DocumentService; pending KYC docs are approved directly by admins.
        if (! $document->isSigned() && ! ($isAdmin && $isKycReview)) {
            return back()->with('error', 'El documento debe estar firmado antes de poder aprobarlo');
        }

        try {
            if ($isAdmin && $isKycReview) {
                $document->markAsApproved(Auth::id(), $request->input('notes'));
                $this->maybeApproveUserFromKyc($document);
            } else {
                DocumentService::approveDocument($document, Auth::id(), $request->input('notes'));
            }
            $document->refresh();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Documento aprobado exitosamente',
                    'document' => $document,
                    'status' => $document->status,
                ]);
            }

            return back()->with('success', 'Documento aprobado correctamente.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al aprobar el documento: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Error al aprobar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Reject a document (admin only)
     */
    public function reject(Request $request, Document $document)
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403, 'Solo administradores pueden rechazar documentos.');
        }
        $this->authorizeAccess($document);

        try {
            $document->update([
                'status'      => 'rejected',
                'approved_at' => null,
                'approved_by' => Auth::id(),
                'notes'       => $request->input('notes', $document->notes),
            ]);

            // If a KYC doc was rejected, mark the user as rejected too
            if (in_array($document->document_type, ['id_front', 'id_back'])) {
                $userId = $document->reservation?->user_id ?? data_get($document->metadata, 'user_id');
                if ($userId && \Illuminate\Support\Facades\Schema::hasColumn('users', 'verification_status')) {
                    \App\Models\User::where('id', $userId)->update(['verification_status' => 'rejected']);
                }
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'status' => $document->status]);
            }
            return back()->with('success', 'Documento rechazado.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al rechazar: ' . $e->getMessage());
        }
    }

    /**
     * If both id_front and id_back are approved, mark the user as verified.
     */
    private function maybeApproveUserFromKyc(Document $document): void
    {
        $userId = $document->reservation?->user_id ?? data_get($document->metadata, 'user_id');
        if (! $userId) return;

        $kycDocs = Document::whereIn('document_type', ['id_front', 'id_back'])
            ->where(function ($q) use ($userId) {
                $q->where('metadata->user_id', $userId)
                  ->orWhereHas('reservation', fn($r) => $r->where('user_id', $userId));
            })
            ->get();

        $approvedTypes = $kycDocs->where('status', 'approved')->pluck('document_type')->unique();
        if ($approvedTypes->contains('id_front') && $approvedTypes->contains('id_back')) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'verification_status')) {
                \App\Models\User::where('id', $userId)->update(['verification_status' => 'approved']);
            }
        }
    }
    
    /**
     * Get documents for a reservation
     */
    public function getDocuments(Reservation $reservation)
    {
        // Check if user has access to this reservation
        $this->authorizeReservationAccess($reservation);
        
        $documents = $reservation->documents()->with(['signedByUser', 'approvedByUser'])->get();
        
        return response()->json([
            'success' => true,
            'documents' => $documents,
            'summary' => DocumentService::getDocumentSummary($reservation)
        ]);
    }
    
    /**
     * Upload a document
     */
    public function upload(Request $request, Reservation $reservation)
    {
        // Check if user has access to this reservation
        $this->authorizeReservationAccess($reservation);
        
        $request->validate([
            'document_type' => 'required|string',
            'title' => 'required|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB
        ]);
        
        try {
            // Store file
            $file = $request->file('file');
            $filePath = DocumentService::storeFile($file);
            
            // Create document record
            $document = DocumentService::createDocument(
                $reservation,
                $request->document_type,
                $request->title,
                $filePath,
                $file->getClientOriginalName(),
                $request->input('metadata', [])
            );
            
            // Mark as generated since file is uploaded
            $document->markAsGenerated();
            
            return redirect()->back()->with('success', 'Documento subido exitosamente.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al subir el documento: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a document
     */
    public function delete(Document $document)
    {
        // Check if user has access to this document
        $this->authorizeAccess($document);
        
        try {
            DocumentService::deleteDocument($document);
            
            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el documento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mark document as conforme
     */
    public function markConforme(Request $request, Reservation $reservation)
    {
        // Check if user has access to this reservation
        $this->authorizeReservationAccess($reservation);
        
        try {
            $validated = $request->validate([
                'observaciones' => 'nullable|string',
                'conforme' => 'required|boolean'
            ]);
            
            // Get the contract document
            $contract = $reservation->documents()->where('document_type', 'contract')->first();
            
            if (!$contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento de contrato no encontrado'
                ], 404);
            }
            
            // Update metadata with conforme status
            $metadata = $contract->metadata ?? [];
            $metadata['conforme'] = $validated['conforme'];
            $metadata['observaciones'] = $validated['observaciones'];
            $metadata['conforme_at'] = now()->toDateTimeString();
            $metadata['conforme_by'] = Auth::id();
            
            $contract->update([
                'metadata' => $metadata,
                'notes' => $validated['observaciones']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Contrato marcado como conforme exitosamente',
                'document' => $contract->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como conforme: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize documents for reservation
     */
    public function initialize(Reservation $reservation)
    {
        // Check if user has access to this reservation
        $this->authorizeReservationAccess($reservation);
        
        try {
            $documents = DocumentService::initializeDocuments($reservation);
            
            return response()->json([
                'success' => true,
                'message' => 'Documentos inicializados exitosamente',
                'documents' => $documents
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al inicializar documentos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if user has access to document
     */
    private function authorizeAccess(Document $document)
    {
        // Admins can always access any document
        if (Auth::user()?->role === 'admin') return;

        $reservation = $document->reservation;
        if ($reservation) {
            $this->authorizeReservationAccess($reservation);
            return;
        }

        // Unlinked docs (e.g. KYC uploaded at register) — only the owning user may access
        $ownerId = data_get($document->metadata, 'user_id');
        if (! $ownerId || Auth::id() !== (int) $ownerId) {
            abort(403, 'Unauthorized');
        }
    }
    
    /**
     * Save contract observations
     */
    public function saveObservations(Request $request, Reservation $reservation)
    {
        // Check if user has access to this reservation
        $this->authorizeReservationAccess($reservation);
        
        try {
            $validated = $request->validate([
                'observaciones' => 'nullable|string',
                'document_type' => 'required|string'
            ]);
            
            // Get the contract document
            $contract = $reservation->documents()->where('document_type', $validated['document_type'])->first();
            
            if (!$contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }
            
            // Update metadata with observations
            $metadata = $contract->metadata ?? [];
            $metadata['observaciones'] = $validated['observaciones'];
            $metadata['observaciones_at'] = now()->toDateTimeString();
            $metadata['observaciones_by'] = Auth::id();
            
            $contract->update([
                'metadata' => $metadata,
                'notes' => $validated['observaciones']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Observaciones guardadas exitosamente',
                'document' => $contract->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar observaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user has access to reservation
     */
    private function authorizeReservationAccess(Reservation $reservation)
    {
        // For now, just check if user owns the reservation or is admin
        if (Auth::id() !== $reservation->user_id && Auth::user()?->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }
}
