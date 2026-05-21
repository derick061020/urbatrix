<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitHistory extends Model
{
    protected $fillable = ['unit_id', 'datetime', 'action', 'author', 'author_role'];
    protected $casts = ['datetime' => 'datetime'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
