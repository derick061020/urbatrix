<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'first_name',
    'last_name',
    'email',
    'password',
    'role',
    'last_seen',
    'google_id',
    'apple_id',
    'avatar',
    'phone',
    'country',
    'verification_status',
    'kyc_id_document',
    'kyc_id_document_back',

    'crm_id',
    'position',
    'company',
    'contact_type',
    'responsible',
    'broker',
    'agency',
    'project',

    'birthdate',
    'document_type',
    'document_number',
    'document_issue_date',
    'document_issue_place',
    'nationality',
    'age',
    'marital_status',
    'gender',
    'birth_place',

    'profession',
    'occupation',
    'depends_on_third',

    'address',
    'city',
    'province',
    'sector',
    'country_residence',
    'country_address',
    'building',
    'apartment',
    'postal_code',

    'spouse_name',
    'spouse_nationality',
    'spouse_document',

    'crm_raw',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'first_name', 'last_name', 'email', 'password', 'role',
        'last_seen', 'google_id', 'apple_id', 'avatar',
        'phone', 'country', 'verification_status', 'kyc_id_document', 'kyc_id_document_back',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen' => 'datetime',
            // 2FA: el secreto y los códigos se guardan cifrados en la BD.
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'crm_raw' => 'array',
        ];
    }

    /* ============================ 2FA / TOTP ============================ */

    /** ¿El usuario tiene la autenticación de dos factores activa y confirmada? */
    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret)
            && ! is_null($this->two_factor_confirmed_at);
    }

    /** Devuelve los códigos de respaldo aún disponibles. */
    public function recoveryCodes(): array
    {
        return $this->two_factor_recovery_codes ?? [];
    }

    /** Genera un nuevo lote de 8 códigos de respaldo (formato XXXX-XXXX). */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))->map(function () {
            return strtoupper(\Illuminate\Support\Str::random(4) . '-' . \Illuminate\Support\Str::random(4));
        })->all();
    }

    /**
     * Consume un código de respaldo (si coincide) eliminándolo del lote.
     * Devuelve true si el código era válido.
     */
    public function consumeRecoveryCode(string $code): bool
    {
        $code  = strtoupper(trim($code));
        $codes = $this->recoveryCodes();
        $index = array_search($code, array_map('strtoupper', $codes), true);
        if ($index === false) {
            return false;
        }
        unset($codes[$index]);
        $this->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();
        return true;
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

    public function hasKycDocumentBack(): bool
    {
        if (! $this->kyc_id_document_back) return false;
        return \Illuminate\Support\Facades\Storage::disk('public')->exists($this->kyc_id_document_back);
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
