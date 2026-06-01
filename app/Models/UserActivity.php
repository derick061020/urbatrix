<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $fillable = [
        'user_id', 'type', 'description', 'subject_type', 'subject_id',
        'meta', 'duration_seconds', 'last_activity_at',
    ];

    protected $casts = [
        'meta'             => 'array',
        'last_activity_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /** primeicon según el tipo de actividad (para el feed). */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'login'             => 'pi-sign-in',
            'property_view'     => 'pi-home',
            'document_view'     => 'pi-file',
            'document_download' => 'pi-download',
            'payment'           => 'pi-dollar',
            'kyc_upload'        => 'pi-id-card',
            'message'           => 'pi-comment',
            default             => 'pi-circle',
        };
    }
}
