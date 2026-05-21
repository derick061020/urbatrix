<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'admin_notes',
        'phone',
        'country',
        'address',
        'province',
        'neighborhood',
        'city',
        'building_name',
        'apartment_number',
        'postal_code',
        'profession',
        'occupation',
        'economic_dependent',
        'payment_method',
        'payment_initial_percentage',
        'payment_construction_percentage',
        'payment_delivery_percentage',
        'legal_costs',
        'payment_installments',
        'budget_status',
        'budget_sent_at',
        'budget_configured_by',
        'budget_notes',
        'budget_observations',
        'budget_client_response_at',
        'terms_accepted',
        'id_document_path',
        'expedition_date',
        'expedition_place',
        'birth_date',
        'age',
        'nationality',
        'marital_status',
        'spouse_name',
        'spouse_nationality',
        'spouse_document',
        'id_type',
        'document_number',
        'unit_id',
        'unit_name',
        'unit_price',
        'reservation_code',
        'status',
        'expires_at',
        'user_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'expires_at' => 'datetime',
        'budget_sent_at' => 'datetime',
        'budget_observations' => 'array',
        'budget_client_response_at' => 'datetime',
        'terms_accepted' => 'boolean',
        'expedition_date' => 'date',
        'birth_date' => 'date',
        'age' => 'integer',
    ];

    public function isBudgetSent(): bool
    {
        return $this->budget_status === 'sent';
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function getReservationStatusAttribute()
    {
        return $this->status;
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->unit_price, 0, '.', ' ');
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Unified pipeline stage label + color used by both the expediente list and the detail view.
     * Returns [string label, string colorToken, int step] where colorToken is one of: ok | warn | info | err | ink-500.
     */
    public function pipelineStage(): array
    {
        $docs = $this->relationLoaded('documents') ? $this->documents : $this->documents()->get();
        $payments = $this->relationLoaded('payments') ? $this->payments : $this->payments()->get();

        $hasOverdue = $payments->where('status', 'overdue')->count() > 0;
        $contractSigned = in_array($this->status, ['contract_signed', 'signed']);
        $signedCount = $docs->where('status', 'signed')->count();
        $kyc = $docs->firstWhere('document_type', 'kyc');
        $kycApproved = $kyc && $kyc->status === 'approved';
        $kycPending  = $kyc && in_array($kyc->status, ['pending', 'generated']);

        $budgetApproved = $this->budget_status === 'approved';

        if ($hasOverdue)             return ['Pago vencido',         'err',  5];
        if ($contractSigned)         return ['Contrato firmado',     'ok',   6];
        if ($budgetApproved && $signedCount > 0)
                                     return ['Documentos firmados',  'info', 4];
        if ($budgetApproved)         return ['Plan de pagos aceptado', 'ok',   4];
        if ($this->isBudgetSent())   return ['Presupuesto enviado',  'warn', 3];
        if ($kycApproved)            return ['KYC aprobado',         'ok',   3];
        if ($kycPending)             return ['KYC pendiente',        'warn', 2];
        return ['Reserva', 'info', 1];
    }
}
