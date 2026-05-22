<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitView extends Model
{
    protected $fillable = ['unit_id', 'user_id', 'session_id', 'ip', 'user_agent', 'viewed_at'];

    protected $casts = ['viewed_at' => 'datetime'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
