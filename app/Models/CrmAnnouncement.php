<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmAnnouncement extends Model
{
    public const AUDIENCES = ['equipo', 'compradores', 'brokers'];

    protected $fillable = ['title', 'body', 'audience', 'pinned', 'archived_at', 'user_id'];

    protected $casts = [
        'audience'    => 'array',
        'pinned'      => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }
}
