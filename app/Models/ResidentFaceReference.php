<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentFaceReference extends Model
{
    protected $fillable = [
        'resident_id',
        'pendataan_document_id',
        'source',
        'face_index',
        'descriptor',
        'extracted_at',
    ];

    protected function casts(): array
    {
        return [
            'descriptor' => 'array',
            'extracted_at' => 'datetime',
            'face_index' => 'integer',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function pendataanDocument(): BelongsTo
    {
        return $this->belongsTo(PendataanDocument::class);
    }
}
