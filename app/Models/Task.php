<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'responsible', 'area', 'due_date',
        'priority', 'status', 'reservation_id', 'project_id', 'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getDueLabelAttribute()
    {
        if (!$this->due_date) return '—';
        $d = $this->due_date;
        if ($d->isToday()) return 'Hoy';
        if ($d->isTomorrow()) return 'Mañana';
        return $d->format('d M');
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completada';
    }
}
