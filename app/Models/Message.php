<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 'sender_id', 'sender_role', 'body', 'channel', 'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isFromAdmin(): bool
    {
        return $this->sender_role === 'admin';
    }

    public function isFromClient(): bool
    {
        return $this->sender_role === 'client';
    }
}
