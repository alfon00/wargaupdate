<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SuratIdentityVerification extends Model
{
    protected $fillable = [
        'resident_id',
        'application_id',
        'selfie_path',
        'match_distance',
        'match_source',
        'reference_document_id',
        'verified_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'match_distance' => 'float',
            'verified_at' => 'datetime',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function referenceDocument(): BelongsTo
    {
        return $this->belongsTo(PendataanDocument::class, 'reference_document_id');
    }

    public function selfieExists(): bool
    {
        return Storage::disk('local')->exists($this->selfie_path);
    }
}
