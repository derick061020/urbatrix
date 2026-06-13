<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCommunication extends Model
{
    use HasFactory;

    protected $table = 'project_communications';

    protected $fillable = ['project_id', 'comm_code', 'channel', 'enabled'];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * ¿Está habilitada una comunicación para un proyecto/canal?
     *
     * Reglas: el proyecto debe estar activo (no en silencio) y el canal del
     * tipo encendido. Las familias 'locked' (legal/seguridad) siempre van.
     */
    public static function isEnabled(int $projectId, string $code, string $channel): bool
    {
        // Comunicaciones obligatorias por ley: siempre activas.
        foreach (config('crm_communications.families', []) as $family) {
            if (empty($family['locked'])) continue;
            foreach ($family['types'] as $type) {
                if ($type['code'] === $code) return true;
            }
        }

        $project = Project::find($projectId);
        if (!$project || !$project->comms_active) return false;

        return static::where('project_id', $projectId)
            ->where('comm_code', $code)
            ->where('channel', $channel)
            ->where('enabled', true)
            ->exists();
    }
}
