<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrokerMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'format',
        'file_path',
        'external_url',
        'file_size',
        'visible',
        'downloads',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'visible' => 'boolean',
    ];

    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }

    /** primeicon para el formato del recurso. */
    public function getIconAttribute(): string
    {
        return match (strtoupper($this->format)) {
            'PDF'         => 'pi-file-pdf',
            'ZIP', 'IMG'  => 'pi-images',
            'MP4'         => 'pi-video',
            'XLSX', 'XLS' => 'pi-file-excel',
            'DOCX', 'DOC' => 'pi-file-word',
            default       => 'pi-file',
        };
    }

    /** clase de color (tokens del layout) para el badge de formato. */
    public function getBadgeColorAttribute(): string
    {
        return match (strtoupper($this->format)) {
            'PDF'         => 'bg-err-soft text-err',
            'ZIP', 'IMG'  => 'bg-info-soft text-info',
            'MP4'         => 'bg-warn-soft text-warn',
            'XLSX', 'XLS' => 'bg-ok-soft text-ok-dark',
            default       => 'bg-ink-100 text-ink-600',
        };
    }

    public function downloadUrl(): ?string
    {
        if ($this->external_url) {
            return $this->external_url;
        }
        return $this->file_path ? route('broker.materials.download', $this) : null;
    }

    /** URL directa al archivo para previsualización en línea (sin contar descarga). */
    public function fileUrl(): ?string
    {
        if ($this->external_url) {
            return $this->external_url;
        }
        return $this->file_path ? \Storage::disk('public')->url($this->file_path) : null;
    }

    /** Tipo de previsualización admitida: pdf | image | video | null. */
    public function previewKind(): ?string
    {
        return match (strtoupper((string) $this->format)) {
            'PDF'                                => 'pdf',
            'IMG', 'PNG', 'JPG', 'JPEG', 'GIF', 'WEBP' => 'image',
            'MP4', 'WEBM', 'MOV'                 => 'video',
            default                              => null,
        };
    }
}
