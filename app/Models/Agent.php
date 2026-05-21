<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'license',
        'active',
        'bio',
        'commission_rate'
    ];

    protected $casts = [
        'active' => 'boolean',
        'commission_rate' => 'decimal:2'
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
