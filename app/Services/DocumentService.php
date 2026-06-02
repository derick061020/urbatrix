<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Create a new document record
     */
    public static function createDocument(Reservation $reservation, $type, $title, $filePath = null, $filename = null, $metadata = [])
    {
        return $reservation->documents()->create([
            'document_type' => $type,
            'title' => $title,
            'filename' => $filename ?? self::generateFilename($type, $title),
            'file_path' => $filePath ?? 'pending',
            'status' => 'pending',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Generate payment plan document
     */
    public static function generatePaymentPlan(Reservation $reservation)
    {
        // Check if already exists
        $existing = $reservation->documents()->ofType('payment_plan')->first();
        if ($existing) {
            return $existing;
        }

        // Create document record as pending (file will be generated when signed)
        $document = self::createDocument(
            $reservation,
            'payment_plan',
            'Plan de Pagos - ' . $reservation->reservation_code,
            null,
            'plan_de_pagos_' . $reservation->reservation_code . '.pdf',
            [
                'plan_type' => $reservation->payment_method,
            ]
        );

        return $document;
    }

    /**
     * Generate purchase promise document
     */
    public static function generatePurchasePromise(Reservation $reservation)
    {
        // Check if already exists
        $existing = $reservation->documents()->ofType('purchase_promise')->first();
        if ($existing) {
            return $existing;
        }

        // Create document record as pending (file will be generated when signed)
        $document = self::createDocument(
            $reservation,
            'purchase_promise',
            'Promesa de Compraventa - ' . $reservation->reservation_code,
            null,
            'promesa_compraventa_' . $reservation->reservation_code . '.pdf',
            [
                'unit_name' => $reservation->unit_name,
                'unit_price' => $reservation->unit_price,
            ]
        );

        return $document;
    }

    /**
     * Sign a document
     */
    public static function signDocument(Document $document, $userId = null, $notes = null)
    {
        $document->markAsSigned($userId, $notes);

        $reservation = $document->reservation;
        $paymentPlan = $reservation->documents()->ofType('payment_plan')->first();
        $purchasePromise = $reservation->documents()->ofType('purchase_promise')->first();

        // As soon as the payment plan is signed, materialize the installment
        // calendar so the client sees the cuotas in "Plan de Pagos". The
        // purchase_promise signing comes later and shouldn't be a blocker.
        // NOTE: the reservation deposit (seña) is recorded as a paid payment at
        // reservation time, so we must NOT gate on the total payment count —
        // doing so would leave the seña as the only payment and the schedule
        // would never be generated. Gate on the absence of an unpaid schedule.
        if ($paymentPlan && $paymentPlan->isSigned()
            && $reservation->payments()->where('status', '!=', 'paid')->count() === 0) {
            try {
                $paymentsCount = \App\Services\PaymentService::generatePayments($reservation);
                \Illuminate\Support\Facades\Log::info('Payments generated after payment_plan signed: ' . $paymentsCount);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Could not generate payments: ' . $e->getMessage());
            }
        }

        // Once both documents are signed, promote the reservation status.
        if ($paymentPlan && $purchasePromise &&
            $paymentPlan->isSigned() && $purchasePromise->isSigned()) {
            $reservation->update(['status' => 'contract_signed']);
        }

        return $document;
    }

    /**
     * Approve a document
     */
    public static function approveDocument(Document $document, $userId = null, $notes = null)
    {
        return $document->markAsApproved($userId, $notes);
    }

    /**
     * Get document status summary
     */
    public static function getDocumentSummary(Reservation $reservation)
    {
        $documents = $reservation->documents;
        
        return [
            'total' => $documents->count(),
            'pending' => $documents->where('status', 'pending')->count(),
            'generated' => $documents->where('status', 'generated')->count(),
            'signed' => $documents->where('status', 'signed')->count(),
            'approved' => $documents->where('status', 'approved')->count(),
            'rejected' => $documents->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Check if all required documents are signed
     */
    public static function allDocumentsSigned(Reservation $reservation)
    {
        $paymentPlan = $reservation->documents()->ofType('payment_plan')->first();
        $purchasePromise = $reservation->documents()->ofType('purchase_promise')->first();

        return $paymentPlan && $purchasePromise && 
               $paymentPlan->isSigned() && $purchasePromise->isSigned();
    }

    /**
     * Get document by type
     */
    public static function getDocumentByType(Reservation $reservation, $type)
    {
        return $reservation->documents()->ofType($type)->first();
    }

    /**
     * Generate filename
     */
    private static function generateFilename($type, $title)
    {
        $slug = Str::slug($title);
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        return "{$type}_{$slug}_{$timestamp}.docx";
    }

    /**
     * Store uploaded file
     */
    public static function storeFile($file, $directory = 'documents')
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');
        
        return $path;
    }

    /**
     * Delete document and file
     */
    public static function deleteDocument(Document $document)
    {
        // Delete file if exists
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        // Delete document record
        $document->delete();
        
        return true;
    }

    /**
     * Get document download link
     */
    public static function getDownloadLink(Document $document)
    {
        if (!$document->file_path) {
            return null;
        }
        
        return route('documents.download', $document->id);
    }

    /**
     * Fix inconsistent document states
     */
    public static function fixDocumentStates(Reservation $reservation)
    {
        $fixed = 0;
        
        foreach ($reservation->documents as $document) {
            // If document has signed_at but status is not signed
            if ($document->signed_at && $document->status !== 'signed') {
                $document->update(['status' => 'signed']);
                $fixed++;
            }
            
            // If document has approved_at but status is not approved
            if ($document->approved_at && $document->status !== 'approved') {
                $document->update(['status' => 'approved']);
                $fixed++;
            }
        }
        
        return $fixed;
    }

    /**
     * Initialize documents for reservation
     */
    public static function initializeDocuments(Reservation $reservation)
    {
        $documents = [];
        
        // Create payment plan document
        $documents['payment_plan'] = self::generatePaymentPlan($reservation);
        
        // Create purchase promise document
        $documents['purchase_promise'] = self::generatePurchasePromise($reservation);
        
        return $documents;
    }
}
