<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PendataanDocument extends Model
{
    protected $fillable = [
        'household_id',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
        'face_extraction_error',
        'face_extracted_at',
    ];

    protected function casts(): array
    {
        return [
            'face_extracted_at' => 'datetime',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function typeLabel(): string
    {
        return match ($this->document_type) {
            'kk' => 'Kartu Keluarga (KK)',
            'ktp_kepala' => 'KTP Kepala KK',
            'selfie_kepala' => 'Selfie verifikasi kepala keluarga',
            'lampiran' => 'Lampiran tambahan',
            default => str_starts_with($this->document_type, 'kia_a')
                ? 'KIA anggota '.((int) substr($this->document_type, 5) + 1)
                : (str_starts_with($this->document_type, 'ktp_a')
                    ? 'KTP anggota '.((int) substr($this->document_type, 5) + 1)
                    : ($this->original_name ?: 'Dokumen')),
        };
    }

    public function isImage(): bool
    {
        $mime = $this->mime_type ?: Storage::disk('local')->mimeType($this->file_path);

        return is_string($mime) && str_starts_with($mime, 'image/');
    }

    public function isPdf(): bool
    {
        $mime = $this->mime_type ?: Storage::disk('local')->mimeType($this->file_path);

        return $mime === 'application/pdf'
            || str_ends_with(strtolower($this->original_name ?? ''), '.pdf');
    }

    public function fileExists(): bool
    {
        return Storage::disk('local')->exists($this->file_path);
    }
}
