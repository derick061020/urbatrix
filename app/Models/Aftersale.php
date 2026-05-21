<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Aftersale extends Model
{
    use HasFactory;

    protected $table = 'aftersales';

    protected $fillable = [
        'type', 'client_name', 'unit_label', 'status',
        'scheduled_date', 'reservation_id', 'unit_id', 'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
