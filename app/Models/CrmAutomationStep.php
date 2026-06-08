<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una fase de una cadena de automatización: envía una plantilla por sus canales
 * tras un retraso medido respecto del paso anterior (o del disparo, si es el
 * primero de la cadena).
 */
class CrmAutomationStep extends Model
{
    use HasFactory;

    protected $table = 'crm_automation_steps';

    protected $fillable = [
        'automation_id',
        'position',
        'template_id',
        'delay_minutes',
        'channels',
    ];

    protected $casts = [
        'channels' => 'array',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(CrmAutomation::class, 'automation_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CrmTemplate::class, 'template_id');
    }

    public function delayLabel(): string
    {
        $min = (int) $this->delay_minutes;
        if ($min <= 0) return 'Inmediato';
        if ($min < 60) return $min . ' min';
        $hours = intdiv($min, 60);
        if ($hours < 24) return $hours . ' h' . ($min % 60 ? ' ' . ($min % 60) . ' min' : '');
        return intdiv($hours, 24) . ' d' . ($hours % 24 ? ' ' . ($hours % 24) . ' h' : '');
    }
}
