<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'document_type',
        'title',
        'filename',
        'file_path',
        'status',
        'generated_at',
        'signed_at',
        'approved_at',
        'signed_by',
        'approved_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'signed_at' => 'datetime',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the reservation that owns the document.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the user who signed the document.
     */
    public function signedByUser()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    /**
     * Get the user who approved the document.
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if document is generated
     */
    public function isGenerated()
    {
        return $this->status === 'generated';
    }

    /**
     * Check if document is signed
     */
    public function isSigned()
    {
        return $this->status === 'signed';
    }

    /**
     * Check if document is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if document is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Mark as generated
     */
    public function markAsGenerated($filePath = null, $filename = null)
    {
        $this->update([
            'status' => 'generated',
            'generated_at' => now(),
            'file_path' => $filePath ?? $this->file_path,
            'filename' => $filename ?? $this->filename,
        ]);
    }

    /**
     * Mark as signed
     */
    public function markAsSigned($userId = null, $notes = null)
    {
        $this->update([
            'status' => 'signed',
            'signed_at' => now(),
            'signed_by' => $userId ?? auth()->id(),
            'notes' => $notes ?? $this->notes,
        ]);
        
        // Refresh to ensure the model has the updated status
        $this->refresh();
    }

    /**
     * Mark as approved
     */
    public function markAsApproved($userId = null, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $userId ?? auth()->id(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Get status label with color
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Pendiente</span>',
            'generated' => '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Generado</span>',
            'signed' => '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Firmado</span>',
            'approved' => '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Aprobado</span>',
            'rejected' => '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Rechazado</span>',
            default => '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">Desconocido</span>',
        };
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute()
    {
        return asset($this->file_path);
    }

    /**
     * Get download URL
     */
    public function getDownloadUrlAttribute()
    {
        return route('documents.download', $this->id);
    }

    /**
     * Scope for document type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope for status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
