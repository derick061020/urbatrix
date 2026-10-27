<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Diligencia debida (CDD) de una reserva. Ver la migración para las fases.
 * El formulario KYC va cifrado (lleva cuentas bancarias y documentos de
 * identidad); los ficheros, en el disco local, fuera del público.
 */
class CddFile extends Model
{
    protected $fillable = [
        'reservation_id', 'buyer_type',
        'screening_status', 'screening_notes', 'screened_at', 'screened_by',
        'kyc_status', 'kyc_form', 'kyc_docs', 'info_request', 'kyc_requested_at', 'kyc_submitted_at',
        'risk_rating', 'risk_notes', 'cdd_decision', 'cdd_notes', 'cdd_decided_at', 'cdd_decided_by',
        'manager_decision', 'manager_notes', 'deviation_note', 'manager_decided_at', 'manager_decided_by',
        'spa_signed_at', 'fiu_reported_at', 'fiu_reference',
        'predeed_reviewed_at', 'predeed_reviewed_by', 'predeed_notes', 'deed_date', 'log',
    ];

    protected $casts = [
        'kyc_form'            => 'encrypted:array',
        'kyc_docs'            => 'array',
        'log'                 => 'array',
        'screened_at'         => 'datetime',
        'kyc_requested_at'    => 'datetime',
        'kyc_submitted_at'    => 'datetime',
        'cdd_decided_at'      => 'datetime',
        'manager_decided_at'  => 'datetime',
        'predeed_reviewed_at' => 'datetime',
        'spa_signed_at'       => 'date',
        'fiu_reported_at'     => 'date',
        'deed_date'           => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public static function enabled(): bool
    {
        return (bool) config('company.cdd.enabled');
    }

    /** Precio de compra en florines (AWG), para el umbral de la FIU. */
    public function purchaseAwg(): float
    {
        $usd = (float) ($this->reservation?->unit?->price ?? $this->reservation?->unit_price ?? 0);

        return $usd * (float) config('company.cdd.awg_per_usd', 1.79);
    }

    public function fiuRequired(): bool
    {
        return $this->purchaseAwg() >= (float) config('company.cdd.fiu_threshold_awg', 500000);
    }

    /** Cinco días hábiles desde la firma del contrato (sin fines de semana; festivos a mano). */
    public function fiuDueDate(): ?Carbon
    {
        if (! $this->spa_signed_at) return null;
        $d = $this->spa_signed_at->copy();
        for ($n = 0; $n < 5;) {
            $d->addDay();
            if (! $d->isWeekend()) $n++;
        }

        return $d;
    }

    public function screeningCleared(): bool
    {
        return $this->screening_status === 'limpio';
    }

    /** El contrato de compraventa sólo con KYC aprobado por cumplimiento y por el director de ventas. */
    public function saleApproved(): bool
    {
        return $this->cdd_decision === 'aprobado' && $this->manager_decision === 'aprobado';
    }

    public function retentionUntil(): ?Carbon
    {
        return $this->deed_date?->copy()->addYears((int) config('company.cdd.retention_years', 10));
    }

    /** Fase en curso (1-7) del flujo de Siebe Group. */
    public function phase(): int
    {
        return match (true) {
            ! $this->screeningCleared()                                  => 1,
            $this->kyc_status !== 'revisado' || $this->cdd_decision !== 'aprobado' => 2,
            $this->manager_decision !== 'aprobado'                       => 3,
            ! $this->spa_signed_at                                       => 4,
            $this->fiuRequired() && ! $this->fiu_reported_at             => 5,
            ! $this->predeed_reviewed_at                                 => 6,
            ! $this->deed_date                                           => 7,
            default                                                      => 8,
        };
    }

    public function addLog(string $text): void
    {
        $log   = (array) ($this->log ?? []);
        $log[] = ['at' => now()->toIso8601String(), 'by' => auth()->user()?->name, 'text' => $text];
        $this->log = $log;
    }
}
