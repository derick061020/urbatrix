<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'payment_type',
        'installment_number',
        'label',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'overdue_notified_at',
        'payment_method',
        'receipt_path',
        'notes',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the reservation that owns the payment.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the admin who approved the payment.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if payment is paid
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is overdue
     */
    public function isOverdue()
    {
        return $this->status === 'overdue' || 
               ($this->status === 'pending' && $this->due_date < now());
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($paymentMethod = null, $receiptPath = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'receipt_path' => $receiptPath,
        ]);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2, '.', ',');
    }

    /**
     * Get status label with color
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'paid' => '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Pagado</span>',
            'pending' => '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Pendiente</span>',
            'overdue' => '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Vencido</span>',
            'cancelled' => '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">Cancelado</span>',
            default => '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">Desconocido</span>',
        };
    }

    /**
     * Get approval status label
     */
    public function getApprovalStatusLabelAttribute()
    {
        return match($this->approval_status) {
            'approved' => '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Aprobado</span>',
            'rejected' => '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Rechazado</span>',
            'pending' => '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Pendiente</span>',
            default => '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">Desconocido</span>',
        };
    }

    /**
     * Check if payment is pending approval
     */
    public function isPendingApproval()
    {
        return $this->approval_status === 'pending';
    }

    /**
     * Check if payment is approved
     */
    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check if payment is rejected
     */
    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }
}
