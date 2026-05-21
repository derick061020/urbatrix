<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_number',
        'client_name',
        'client_email',
        'client_phone',
        'unit_id',
        'agent_id',
        'deal_price',
        'status',
        'deal_date',
        'notes'
    ];

    protected $casts = [
        'deal_price' => 'decimal:2',
        'deal_date' => 'date'
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
