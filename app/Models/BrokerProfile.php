<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ficha de alta de un broker o agencia. Los datos bancarios van cifrados con
 * la APP_KEY (cast encrypted:array) y los documentos en el disco local, no en
 * el público: sólo se descargan desde el panel del admin.
 */
class BrokerProfile extends Model
{
    protected $fillable = ['user_id', 'kind', 'data', 'fiscal', 'docs', 'status', 'submitted_at', 'reviewed_at', 'reviewed_by'];

    protected $casts = [
        'data'         => 'array',
        'fiscal'       => 'encrypted:array',
        'docs'         => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isAgency(): bool
    {
        return $this->kind === 'agency';
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['en_revision', 'verificado'], true);
    }

    /** Documentos que se piden según el tipo, en el orden de config('company.broker_alta'). */
    public static function requiredDocs(string $kind): array
    {
        return (array) config('company.broker_alta.docs.' . ($kind === 'agency' ? 'agency' : 'broker'), []);
    }
}
