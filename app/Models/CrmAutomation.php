<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'report_uploaded'        => 'Nuevo reporte de obra subido',
        'commission_unlocked'    => 'Comisión desbloqueada (broker)',
        'document_uploaded'      => 'Documento subido',
        'aftersale_created'      => 'Caso de postventa creado',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(CrmTemplate::class, 'template_id');
    }

    /** Fases de la cadena, en orden de ejecución. */
    public function steps(): HasMany
    {
        return $this->hasMany(CrmAutomationStep::class, 'automation_id')->orderBy('position');
    }

    /**
     * Pasos a ejecutar. Si la automatización aún no tiene pasos definidos
     * (datos heredados), sintetiza uno a partir de los campos planos para que
     * el flujo siga funcionando sin migración previa.
     */
    public function resolvedSteps(): Collection
    {
        $steps = $this->relationLoaded('steps') ? $this->steps : $this->steps()->with('template')->get();

        if ($steps->isNotEmpty()) {
            return $steps;
        }

        $legacy = new CrmAutomationStep([
            'automation_id' => $this->id,
            'position'      => 1,
            'template_id'   => $this->template_id,
            'delay_minutes' => $this->delay_minutes ?? 0,
            'channels'      => $this->channels,
        ]);
        $legacy->setRelation('template', $this->template);

        return new Collection([$legacy]);
    }

    public function stepsCount(): int
    {
        return $this->relationLoaded('steps')
            ? max(1, $this->steps->count())
            : max(1, $this->steps()->count());
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
