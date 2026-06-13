<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'stage', 'location', 'progress', 'color', 'icon_path', 'description',
        'comms_active', 'comms_start_date',
    ];

    protected $casts = [
        'progress'         => 'integer',
        'comms_active'     => 'boolean',
        'comms_start_date' => 'date',
    ];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function communications()
    {
        return $this->hasMany(ProjectCommunication::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function constructionReports()
    {
        return $this->hasMany(ConstructionReport::class)->latest('published_at');
    }

    public function getSoldCountAttribute()
    {
        return $this->units()->where('status', 'SOLD')->count();
    }

    public function getReservedCountAttribute()
    {
        return $this->units()->where('status', 'RESERVED')->count();
    }

    public function getAvailableCountAttribute()
    {
        return $this->units()->where('status', 'AVAILABLE')->count();
    }

    public function getTotalUnitsAttribute()
    {
        return $this->units()->count();
    }
}
