<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConstructionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'period',
        'title',
        'description',
        'overall_progress',
        'estimated_delivery',
        'phases',
        'photos',
        'created_by',
        'published_at',
        'notified_at',
        'notified_count',
    ];

    protected $casts = [
        'phases'       => 'array',
        'photos'       => 'array',
        'overall_progress' => 'integer',
        'published_at' => 'datetime',
        'notified_at'  => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
