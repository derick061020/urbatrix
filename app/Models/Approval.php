<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'requested_by', 'amount_or_condition',
        'priority', 'status', 'reservation_id', 'notes', 'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
