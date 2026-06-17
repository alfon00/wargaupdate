<?php

namespace App\Models;

use App\Support\LetterSubjectSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ApplicationDocument extends Model
{
    protected $fillable = [
        'application_id',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function typeLabel(): string
    {
        if (preg_match('/^subject_(\d+)$/', $this->document_type, $matches)) {
            $index = (int) $matches[1];
            $subjects = $this->application?->letterSubjects() ?? [];
            $name = $subjects[$index]['name'] ?? null;

            return LetterSubjectSchema::documentLabelForIndex($index, is_string($name) ? $name : null);
        }

        if (preg_match('/^req_(\d+)$/', $this->document_type, $matches)) {
            $index = (int) $matches[1];
            $fields = $this->application?->serviceType?->required_fields ?? [];

            if (isset($fields[$index]) && is_string($fields[$index])) {
                return $fields[$index];
            }
        }

        if (preg_match('/^lampiran_(\d+)$/', $this->document_type, $matches)) {
            return 'Lampiran '.$matches[1];
        }

        return $this->original_name ?: 'Dokumen';
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
}
