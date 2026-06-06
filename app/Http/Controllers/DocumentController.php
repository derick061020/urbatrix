<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Reservation;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Symfony\Component\Process\Process;

class DocumentController extends Controller
{
    /**
     * Download a document
     */
    public function download(Document $document)
    {
        $document = $this->prepareDocumentForAccess($document, true);
        $absolute = $this->resolveAbsolutePath($document->file_path);
        if (! $absolute) {
            abort(404, 'Documento no encontrado');
        }

        if (auth()->check() && ! auth()->user()->is_admin) {
            \App\Support\ActivityLogger::log(auth()->id(), 'document_download', 'Descargó '.($document->filename ?: 'un documento'), $document);
        }

        // Los documentos HTML imprimibles (plan de pagos / promesa) se sirven inline:
        // el navegador los renderiza y el botón "Descargar PDF" usa window.print().
        if (strtolower(pathinfo($absolute, PATHINFO_EXTENSION)) === 'html') {
            return Response::file($absolute, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        return Response::download($absolute, $document->filename ?: basename($absolute));
    }

    /**
     * Preview a document inline.
     */
    public function preview(Document $document)
    {
        $document = $this->prepareDocumentForAccess($document, false);
        $absolute = $this->resolveAbsolutePath($document->file_path);
        if (! $absolute) {
            abort(404, 'Documento no encontrado');
        }

        if (auth()->check() && ! auth()->user()->is_admin) {
            \App\Support\ActivityLogger::log(auth()->id(), 'document_view', 'Visualizó '.($document->filename ?: 'un documento'), $document);
        }

        $previewPath = $this->preparePreviewFile($absolute);

        $previewFilename = pathinfo($document->filename ?: basename($previewPath), PATHINFO_FILENAME)
            .'.'.pathinfo($previewPath, PATHINFO_EXTENSION);

        return Response::file($previewPath, [
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $previewFilename).'"',
        ]);
    }

    private function prepareDocumentForAccess(Document $document, bool $isDownload = false): Document
    {
        $this->authorizeAccess($document);

        // Solo aplicar restricción del plan de pagos para download, no para preview
        if ($isDownload && $document->document_type === 'payment_plan' && Auth::user()?->role !== 'admin') {
            $reservation = $document->reservation;
            $accepted = $reservation && ($reservation->budget_status === 'approved'
                || in_array($reservation->status, ['contract_signed', 'signed']));
            if (! $accepted) {
                abort(403, 'Tenés que marcar como conforme el plan de pagos antes de descargarlo.');
            }
        }

        // El KYC es un formulario imprimible: se regenera desde los datos que el
        // cliente cargó, salvo que ya esté aprobado (congelado para revisión).
        if ($document->document_type === 'kyc') {
            if ($document->isApproved() || ! $document->reservation) {
                return $document;
            }
            try {
                $path = \App\Helpers\DocumentDataHelper::renderKycHtml($document->reservation);
                $document->update(['file_path' => $path, 'filename' => basename($path)]);
                $document->refresh();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('KYC HTML generation failed: '.$e->getMessage());
            }
            return $document;
        }

        $regenerableTypes = ['payment_plan', 'purchase_promise', 'contract'];
        if (! in_array($document->document_type, $regenerableTypes) || $document->isSigned() || $document->isApproved()) {
            return $document;
        }

        $reservation = $document->reservation;
        if (! $reservation) {
            return $document;
        }

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
            foreach ($paths as $path) {
                if (is_file($path)) return true;
            }
            return false;
        })();

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

        if (! $templateExists && ! $this->resolveAbsolutePath($document->file_path)) {
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

        return $document;
    }

    private function resolveAbsolutePath(?string $relativePath): ?string
    {
        if (! $relativePath || $relativePath === 'pending') {
            return null;
        }

        $candidates = [
            storage_path('app/public/' . ltrim($relativePath, '/')),
            public_path(ltrim($relativePath, '/')),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function preparePreviewFile(string $absolutePath): string
    {
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        if (! in_array($extension, ['doc', 'docx'])) {
            return $absolutePath;
        }

        $previewDir = storage_path('app/document_previews');
        $profileDir = storage_path('app/libreoffice_profile');
        if (! is_dir($previewDir)) {
            mkdir($previewDir, 0755, true);
        }
        if (! is_dir($profileDir)) {
            mkdir($profileDir, 0755, true);
        }

        $previewPath = $previewDir.'/'.md5($absolutePath.'|'.filemtime($absolutePath)).'.pdf';
        if (is_file($previewPath)) {
            return $previewPath;
        }

        $convertedPath = $this->convertWordWithLibreOffice($absolutePath, $previewDir, $profileDir)
            ?: $this->convertWordWithPhpWord($absolutePath, $previewPath);

        if (! $convertedPath || ! is_file($convertedPath)) {
            abort(500, 'No se pudo convertir el documento Word a PDF para previsualizarlo.');
        }

        if ($convertedPath !== $previewPath) {
            rename($convertedPath, $previewPath);
        }

        return $previewPath;
    }

    private function convertWordWithLibreOffice(string $absolutePath, string $previewDir, string $profileDir): ?string
    {
        $binary = $this->findLibreOfficeBinary();
        if ($binary === '') {
            return null;
        }

        $process = new Process([
            $binary,
            '--headless',
            '--nologo',
            '--nofirststartwizard',
            '-env:UserInstallation=file://'.$profileDir,
            '--convert-to',
            'pdf',
            '--outdir',
            $previewDir,
            $absolutePath,
        ], null, [
            'DISPLAY' => false,
        ]);
        $process->setTimeout(60);
        $process->run();

        $convertedPath = $previewDir.'/'.pathinfo($absolutePath, PATHINFO_FILENAME).'.pdf';
        if (! $process->isSuccessful() || ! is_file($convertedPath)) {
            return null;
        }

        return $convertedPath;
    }

    private function convertWordWithPhpWord(string $absolutePath, string $previewPath): ?string
    {
        try {
            Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
            Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

            $phpWord = IOFactory::load($absolutePath);
            IOFactory::createWriter($phpWord, 'PDF')->save($previewPath);

            return is_file($previewPath) ? $previewPath : null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PHPWord PDF preview conversion failed: '.$e->getMessage());
            return null;
        }
    }

    private function findLibreOfficeBinary(): string
    {
        $paths = array_filter(explode(PATH_SEPARATOR, getenv('PATH') ?: ''));
        $candidates = [
            '/usr/bin/soffice',
            '/usr/local/bin/soffice',
            '/usr/bin/libreoffice',
            '/usr/local/bin/libreoffice',
        ];

        foreach ($paths as $path) {
            $candidates[] = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'soffice';
            $candidates[] = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'libreoffice';
        }

        foreach (array_unique($candidates) as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        return '';
    }
    
    /**
     * Sign a document
     */
    public function sign(Request $request, Document $document)
    {
        // Check if user has access to this document
        $this->authorizeAccess($document);

        // Gate: the purchase_promise (and any later contract) can only be signed AFTER
        // the payment plan is signed. The contract additionally requires an explicit
        // acceptance step; the purchase_promise does NOT — for the promise the signature
        // itself implies acceptance, so it is signed directly.
        if (in_array($document->document_type, ['purchase_promise', 'contract'])) {
            $reservation = $document->reservation;
            $planDoc = $reservation?->documents->firstWhere('document_type', 'payment_plan');
            if (! $planDoc || ! in_array($planDoc->status, ['signed', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Primero tenés que firmar el plan de pagos.',
                ], 400);
            }
            if ($document->document_type === 'contract' && empty(data_get($document->metadata, 'accepted_at'))) {
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

        // Always sign locally — mark the doc as signed server-side immediately.
        // (SignNow integration intentionally bypassed: clients sign in-app via the
        // canvas/typed signature in the Acuerdos modal, no external email round-trip.)
        try {
            $notes = $request->input('notes');

            // Enrich the client-supplied signature payload with server-side evidence
            // (IP, user agent and an authoritative timestamp) so the admin can audit
            // exactly when, from where and with which device the document was signed.
            $decoded = json_decode((string) $notes, true);
            if (is_array($decoded)) {
                $decoded['ip'] = $request->ip();
                $decoded['signed_server_at'] = now()->toIso8601String();
                if (empty($decoded['user_agent'])) {
                    $decoded['user_agent'] = $request->userAgent();
                }
                $notes = json_encode($decoded);
            }

            // Embed the signature image into the actual file (append a signature page
            // to the docx). If this fails we still mark the doc as signed — the JSON
            // payload with the signature stays in `notes` as legal evidence.
            $this->embedSignatureInDocument($document, $notes);

            DocumentService::signDocument($document, Auth::id(), $notes);
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
     * Append a signature section (image + signer name + date) to the document's
     * generated docx file and update the document's file_path to point to the
     * new signed version. Best-effort — failures are logged, not thrown.
     */
    private function embedSignatureInDocument(Document $document, ?string $notesJson): void
    {
        if (! $notesJson) return;

        $data = json_decode($notesJson, true);
        if (! is_array($data) || empty($data['signature_image'])) return;

        if (! preg_match('#^data:image/(png|jpe?g);base64,(.+)$#', $data['signature_image'], $m)) {
            return;
        }
        $imageBinary = base64_decode($m[2], true);
        if ($imageBinary === false || $imageBinary === '') return;

        $absolute = $this->resolveAbsolutePath($document->file_path);
        if (! $absolute || ! is_file($absolute)) return;

        $signerName = trim((string)($data['signer_name'] ?? ''));
        $ts = $data['timestamp'] ?? now()->toIso8601String();
        try {
            $when = \Carbon\Carbon::parse($ts)->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            $when = now()->format('d/m/Y H:i');
        }

        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        // Documentos imprimibles (plan de pagos / promesa) ahora se generan como
        // HTML: la firma se inyecta directamente en el recuadro de firma del HTML.
        if (in_array($ext, ['html', 'htm'])) {
            $this->embedSignatureInHtml($absolute, $data['signature_image'], $signerName, $when);
            return;
        }

        if (! in_array($ext, ['doc', 'docx'])) return;

        $tmpDir = storage_path('app/tmp_signatures');
        if (! is_dir($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }
        $sigExt = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $sigPath = $tmpDir . '/sig_' . $document->id . '_' . time() . '.' . $sigExt;
        if (file_put_contents($sigPath, $imageBinary) === false) return;

        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($absolute);

            $section = $phpWord->addSection();
            $section->addTextBreak(1);
            $section->addText('FIRMA DEL CLIENTE', [
                'bold' => true,
                'size' => 13,
                'color' => '171717',
            ], [
                'spaceBefore' => 200,
                'spaceAfter'  => 120,
            ]);

            $section->addImage($sigPath, [
                'width'  => 220,
                'height' => 90,
                'wrappingStyle' => 'inline',
            ]);

            $section->addTextBreak(1);
            if ($signerName !== '') {
                $section->addText('Nombre: ' . $signerName, ['size' => 11]);
            }
            $section->addText('Fecha: ' . $when, ['size' => 11]);
            $section->addText(
                'Documento firmado electrónicamente desde el portal del cliente.',
                ['italic' => true, 'size' => 9, 'color' => '717784']
            );

            $dir = dirname($absolute);
            $base = pathinfo($absolute, PATHINFO_FILENAME);
            $signedAbsolute = $dir . DIRECTORY_SEPARATOR . $base . '_signed.docx';

            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($signedAbsolute);

            if (is_file($signedAbsolute)) {
                $publicRoot = storage_path('app/public') . DIRECTORY_SEPARATOR;
                $signedRelative = str_starts_with($signedAbsolute, $publicRoot)
                    ? str_replace(DIRECTORY_SEPARATOR, '/', substr($signedAbsolute, strlen($publicRoot)))
                    : 'documents/' . basename($signedAbsolute);

                $document->update([
                    'file_path' => $signedRelative,
                    'filename'  => basename($signedAbsolute),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                'Could not embed signature into document ' . $document->id . ': ' . $e->getMessage()
            );
        } finally {
            if (is_file($sigPath)) @unlink($sigPath);
        }
    }

    /**
     * Inyecta la firma del cliente dentro del recuadro de firma del documento
     * HTML imprimible (plan de pagos / promesa). Reemplaza el primer
     * `.sig-box` vacío (el del comprador) por la imagen de la firma y completa
     * la fecha en su etiqueta. Best-effort: ante cualquier fallo, no lanza.
     */
    private function embedSignatureInHtml(string $absolute, string $signatureDataUri, string $signerName, string $when): void
    {
        try {
            $html = @file_get_contents($absolute);
            if ($html === false || $html === '') return;

            // Evitar doble firma si el documento se vuelve a firmar.
            if (str_contains($html, 'data-signature="1"')) {
                return;
            }

            $img = '<img data-signature="1" src="' . htmlspecialchars($signatureDataUri, ENT_QUOTES)
                . '" alt="Firma" style="max-height:46px; max-width:200px; object-fit:contain; display:block; margin:2px auto 0;">';

            $sigBox = '<div class="sig-box" style="height:auto; min-height:38px; display:flex; align-items:flex-end; justify-content:center; padding-bottom:2px;">'
                . $img . '</div>';

            $label = '<div class="sig-label">Firma &middot; Fecha: ' . htmlspecialchars($when, ENT_QUOTES) . '</div>';

            $replaced = 0;
            // Reemplaza el primer recuadro de firma vacío junto con su etiqueta.
            $newHtml = preg_replace(
                '#<div class="sig-box">\s*</div>\s*<div class="sig-label">.*?</div>#s',
                $sigBox . $label,
                $html,
                1,
                $replaced
            );

            // Fallback: si no existe la dupla box+label, reemplaza solo el primer box vacío.
            if ($replaced === 0) {
                $newHtml = preg_replace(
                    '#<div class="sig-box">\s*</div>#s',
                    $sigBox,
                    $html,
                    1,
                    $replaced
                );
            }

            if ($replaced > 0 && is_string($newHtml)) {
                @file_put_contents($absolute, $newHtml);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                'Could not embed signature into HTML document ' . $absolute . ': ' . $e->getMessage()
            );
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
        // Admin-requested docs the client uploaded: reviewed (approved/rejected) directly, not signed.
        $isRequestedReview = data_get($document->metadata, 'requested') && $document->isPending();
        $isAdmin = Auth::user()?->role === 'admin';

        // Signed contracts go through DocumentService; pending KYC / requested docs are approved directly by admins.
        if (! $document->isSigned() && ! ($isAdmin && ($isKycReview || $isRequestedReview))) {
            return back()->with('error', 'El documento debe estar firmado antes de poder aprobarlo');
        }

        try {
            if ($isAdmin && ($isKycReview || $isRequestedReview)) {
                $document->markAsApproved(Auth::id(), $request->input('notes'));
                if ($isKycReview) {
                    $this->maybeApproveUserFromKyc($document);
                }
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
