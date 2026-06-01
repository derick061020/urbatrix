<?php

namespace App\Support;

use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Registra la actividad importante del usuario para el panel de Usuarios → Actividad.
 * Silencioso: nunca interrumpe el flujo de la app.
 */
class ActivityLogger
{
    public static function log(?int $userId, string $type, string $description = '', ?Model $subject = null, array $meta = []): ?UserActivity
    {
        $userId = $userId ?: Auth::id();
        if (! $userId) {
            return null;
        }

        try {
            return UserActivity::create([
                'user_id'          => $userId,
                'type'             => $type,
                'description'      => $description ?: null,
                'subject_type'     => $subject ? $subject->getMorphClass() : null,
                'subject_id'       => $subject?->getKey(),
                'meta'             => $meta ?: null,
                'last_activity_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('ActivityLogger: '.$e->getMessage());
            return null;
        }
    }

    /**
     * Abre una sesión (type=login) y devuelve su id para guardarlo en la sesión HTTP.
     */
    public static function startSession(int $userId): ?int
    {
        return optional(self::log($userId, 'login', 'Inició sesión'))->id;
    }

    /**
     * Late-tick del heartbeat: actualiza la duración de la sesión activa.
     */
    public static function touchSession(?int $activityId): void
    {
        if (! $activityId) {
            return;
        }
        try {
            $session = UserActivity::find($activityId);
            if ($session && $session->type === 'login') {
                $session->update([
                    'last_activity_at' => now(),
                    'duration_seconds' => max(0, now()->diffInSeconds($session->created_at)),
                ]);
            }
        } catch (\Throwable $e) {
            // silencioso
        }
    }
}
