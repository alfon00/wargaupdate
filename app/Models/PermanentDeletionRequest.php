<?php

namespace App\Models;

use App\Enums\PermanentDeletionRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermanentDeletionRequest extends Model
{
    protected $fillable = [
        'request_number',
        'rt_profile_id',
        'requested_by',
        'target_type',
        'resident_id',
        'household_id',
        'target_name',
        'target_nik',
        'family_card_number',
        'signature_path',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PermanentDeletionRequestStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function rtProfile(): BelongsTo
    {
        return $this->belongsTo(RtProfile::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function targetTypeLabel(): string
    {
        return match ($this->target_type) {
            'resident' => 'Warga',
            'household' => 'Kartu keluarga',
            default => 'Data',
        };
    }

    public function isPending(): bool
    {
        return $this->status === PermanentDeletionRequestStatus::Pending;
    }

    /** @param  Builder<PermanentDeletionRequest>  $query */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PermanentDeletionRequestStatus::Pending);
    }
}
