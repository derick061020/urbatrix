<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    public const STATUSES = [
        'abierto'  => 'Abierto',
        'en_curso' => 'En curso',
        'resuelto' => 'Resuelto',
    ];

    protected $fillable = [
        'code', 'user_id', 'guest_token', 'name', 'email', 'comment', 'screenshot',
        'attachments', 'page_url', 'page_title', 'user_agent', 'viewport',
        'status', 'reply', 'replied_by', 'replied_at', 'seen_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'replied_at'  => 'datetime',
        'seen_at'     => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $t) {
            do {
                $t->code = 'T-'.strtoupper(Str::random(6));
            } while (static::where('code', $t->code)->exists());
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replier()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /** Los del usuario con sesión, o los de la cookie del anónimo. */
    public function scopeOwnedBy($q, ?User $user, ?string $guestToken)
    {
        if ($user) {
            return $q->where('user_id', $user->id);
        }
        return $guestToken ? $q->whereNull('user_id')->where('guest_token', $guestToken) : $q->whereRaw('1 = 0');
    }

    public function statusLabel(): string
    {
        return __(self::STATUSES[$this->status] ?? $this->status);
    }

    /** Respuesta del equipo que el usuario aún no ha abierto. */
    public function hasUnseenReply(): bool
    {
        return $this->replied_at && (! $this->seen_at || $this->seen_at->lt($this->replied_at));
    }
}
