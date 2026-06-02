<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrokerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'format',
        'file_path',
        'file_size',
        'downloads',
        'created_by',
    ];

    public function broker()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** primeicon según el formato del documento. */
    public function getIconAttribute(): string
    {
        return match (strtoupper((string) $this->format)) {
            'PDF'                                  => 'pi-file-pdf',
            'ZIP', 'IMG', 'PNG', 'JPG', 'JPEG'     => 'pi-images',
            'MP4', 'WEBM', 'MOV'                   => 'pi-video',
            'XLSX', 'XLS'                          => 'pi-file-excel',
            'DOCX', 'DOC'                          => 'pi-file-word',
            default                                => 'pi-file',
        };
    }

    /** Tipo de previsualización admitida: pdf | image | video | null. */
    public function previewKind(): ?string
    {
        return match (strtoupper((string) $this->format)) {
            'PDF'                                      => 'pdf',
            'IMG', 'PNG', 'JPG', 'JPEG', 'GIF', 'WEBP' => 'image',
            'MP4', 'WEBM', 'MOV'                       => 'video',
            default                                    => null,
        };
    }

    /** URL directa al archivo para previsualización en línea. */
    public function fileUrl(): ?string
    {
        return $this->file_path ? \Storage::disk('public')->url($this->file_path) : null;
    }

    public function downloadUrl(): ?string
    {
        return $this->file_path ? route('broker.documents.download', $this) : null;
    }
}
