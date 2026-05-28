<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'advisor_id',
        'unit_id',
        'scheduled_at',
        'duration_minutes',
        'google_event_id',
        'google_meet_link',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at'     => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
