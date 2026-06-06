<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmTemplate extends Model
{
    use HasFactory;

    protected $table = 'crm_templates';

    protected $fillable = [
        'name',
        'category',
        'icon',
        'channels',
        'subject',
        'body',
        'variables',
        'doc_label',
        'audience',
        'last_used_at',
        'usage_count',
        'is_active',
    ];

    protected $casts = [
        'channels'     => 'array',
        'variables'    => 'array',
        'last_used_at' => 'datetime',
        'is_active'    => 'boolean',
    ];

    public static array $CATEGORIES = [
        'bienvenida'  => 'Bienvenida',
        'seguimiento' => 'Seguimiento',
        'pagos'       => 'Pagos',
        'legal'       => 'Legal',
        'proyectos'   => 'Proyectos',
        'profesional' => 'Profesional',
        'interno'     => 'Interno',
        'otro'        => 'Otro',
    ];

    public static array $ICONS = [
        'file', 'user', 'eye', 'clock', 'file-pdf', 'chart-line', 'check',
        'envelope', 'whatsapp', 'phone', 'bell', 'star', 'heart', 'home',
        'calendar', 'cog',
    ];

    public function automations(): HasMany
    {
        return $this->hasMany(CrmAutomation::class, 'template_id');
    }

    public function categoryLabel(): string
    {
        return self::$CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function channelsLabel(): string
    {
        $map = ['email' => 'Email', 'whatsapp' => 'WhatsApp', 'sms' => 'SMS', 'push' => 'Push'];
        return collect($this->channels ?? [])->map(fn($c) => $map[$c] ?? ucfirst($c))->implode(' + ');
    }

    public function lastUsedLabel(): string
    {
        if (!$this->last_used_at) return 'Sin usar';
        return 'Última vez ' . $this->last_used_at->diffForHumans();
    }
}
