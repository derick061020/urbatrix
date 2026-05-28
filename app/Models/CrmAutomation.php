<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmAutomation extends Model
{
    use HasFactory;

    protected $table = 'crm_automations';

    protected $fillable = [
        'name',
        'description',
        'trigger_event',
        'trigger_conditions',
        'template_id',
        'delay_minutes',
        'channels',
        'is_active',
        'last_run_at',
        'run_count',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'channels'           => 'array',
        'is_active'          => 'boolean',
        'last_run_at'        => 'datetime',
    ];

    public static array $TRIGGER_EVENTS = [
        'reservation_created'    => 'Reserva creada',
        'reservation_confirmed'  => 'Reserva confirmada',
        'kyc_pending'            => 'KYC pendiente',
        'kyc_approved'           => 'KYC aprobado',
        'payment_due_soon'       => 'Cuota próxima a vencer',
        'payment_overdue'        => 'Cuota vencida',
        'payment_received'       => 'Pago recibido',
        'contract_ready'         => 'Contrato listo',
        'contract_signed'        => 'Contrato firmado',
        'progress_update'        => 'Actualización de avance de obra',
        'document_uploaded'      => 'Documento subido',
        'aftersale_created'      => 'Caso de postventa creado',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(CrmTemplate::class, 'template_id');
    }

    public function triggerLabel(): string
    {
        return self::$TRIGGER_EVENTS[$this->trigger_event] ?? $this->trigger_event;
    }

    public function channelsLabel(): string
    {
        $map = ['email' => 'Email', 'whatsapp' => 'WhatsApp', 'sms' => 'SMS', 'push' => 'Push'];
        return collect($this->channels ?? [])->map(fn($c) => $map[$c] ?? ucfirst($c))->implode(' + ');
    }

    public function delayLabel(): string
    {
        if ($this->delay_minutes <= 0) return 'Inmediato';
        if ($this->delay_minutes < 60) return $this->delay_minutes . ' min';
        $hours = intdiv($this->delay_minutes, 60);
        if ($hours < 24) return $hours . ' h';
        return intdiv($hours, 24) . ' d';
    }
}
