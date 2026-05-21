<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $fillable = [
        'unit_id', 'deal_id',
        'created_at_event', 'created_by',
        'modified_at_event', 'modified_by',
        'amount', 'status',
    ];

    protected $casts = [
        'created_at_event' => 'datetime',
        'modified_at_event' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
