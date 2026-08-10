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
        'paid_amount',
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
        'receipt_signature',
        'receipt_signer_name',
        'receipt_signed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
        'receipt_signed_at' => 'datetime',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
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
     * Saldo pendiente de la cuota (monto programado menos lo abonado).
     */
    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    /**
     * Hay un abono parcial: se pagó algo pero queda saldo (deuda).
     */
    public function isPartial(): bool
    {
        return $this->status !== 'paid' && (float) $this->paid_amount > 0;
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
     * Concepto traducible de la cuota.
     *
     * El `label` se persiste en el idioma activo al generar el plan, así que no
     * puede cambiar de idioma por sí solo. Cuando la cuota tiene datos
     * estructurados (`payment_type` / `installment_number`) reconstruimos el
     * texto traducido; si no, caemos al `label` guardado.
     */
    public function getDisplayLabelAttribute(): string
    {
        $stored = trim((string) $this->label);

        // Detalle entre paréntesis del label guardado: "(20% + $5,000 legales)".
        // Son cifras, así que se conservan tal cual en cualquier idioma.
        $detail = preg_match('/\(([^)]*)\)\s*$/u', $stored, $m) ? ' ('.$m[1].')' : '';

        // "Cuota 3 de 24" -> total 24, para no perder el denominador.
        $total = preg_match('/\b(?:de|of)\s+(\d+)\s*$/ui', $stored, $m) ? (int) $m[1] : null;

        return match ($this->payment_type) {
            'reservation'  => __('Reserva').$detail,
            'initial'      => __('Pago inicial').$detail,
            'legal'        => __('Costos legales').$detail,
            'credit'       => __('Saldo a favor').$detail,
            'construction' => __('Pago en construcción').$detail,
            'delivery'     => __('Pago en entrega').$detail,
            'installment'  => match (true) {
                $this->installment_number && $total => __('Cuota :n de :total', ['n' => $this->installment_number, 'total' => $total]),
                (bool) $this->installment_number    => __('Cuota :n', ['n' => $this->installment_number]),
                default                             => __('Cuota'),
            },
            default        => $stored !== '' ? $stored : __('Cuota'),
        };
    }

    /**
     * Get status label with color
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'paid' => '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">'.__('Pagado').'</span>',
            'pending' => '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">'.__('Pendiente').'</span>',
            'overdue' => '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">'.__('Vencido').'</span>',
            'cancelled' => '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">'.__('Cancelado').'</span>',
            default => '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">'.__('Desconocido').'</span>',
        };
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute()
    {
        return match($this->payment_method) {
            'cash' => __('Efectivo'),
            'transfer' => __('Transferencia'),
            'check' => __('Cheque'),
            'card' => __('Tarjeta'),
            'wire' => __('Transferencia bancaria'),
            'other' => __('Otro'),
            default => $this->payment_method ?: __('No especificado'),
        };
    }

    /**
     * Get approval status label
     */
    public function getApprovalStatusLabelAttribute()
    {
        return match($this->approval_status) {
            'approved' => '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">'.__('Aprobado').'</span>',
            'rejected' => '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">'.__('Rechazado').'</span>',
            'pending' => '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">'.__('Pendiente').'</span>',
            default => '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">'.__('Desconocido').'</span>',
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
