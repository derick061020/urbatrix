<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'first_name', 'last_name', 'email', 'password', 'role', 'last_seen', 'google_id', 'apple_id', 'avatar', 'phone', 'country', 'verification_status', 'kyc_id_document'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'first_name', 'last_name', 'email', 'password', 'role',
        'last_seen', 'google_id', 'apple_id', 'avatar',
        'phone', 'country', 'verification_status', 'kyc_id_document',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen' => 'datetime',
        ];
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin';
    }

    public function isBroker(): bool
    {
        return $this->role === 'broker';
    }

    public function assignedUnits()
    {
        return $this->belongsToMany(Unit::class, 'broker_unit')->withTimestamps();
    }

    public function brokerDocuments()
    {
        return $this->hasMany(BrokerDocument::class)->latest();
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function hasActiveReservation(): bool
    {
        return $this->reservations()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereRaw('LOWER(status) <> ?', ['cancelled']);
            })
            ->exists();
    }

    public function postAuthRedirectPath(): string
    {
        if ($this->role === 'broker') {
            return '/broker';
        }
        return $this->hasActiveReservation() ? '/dashboard' : '/';
    }

    public function hasKycDocument(): bool
    {
        if (! $this->kyc_id_document) return false;
        return \Illuminate\Support\Facades\Storage::disk('public')->exists($this->kyc_id_document);
    }

    public function isPendingVerification(): bool
    {
        return $this->verification_status === 'pending';
    }

    public function isApproved(): bool
    {
        return ($this->verification_status ?? 'approved') === 'approved';
    }
}
