<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Household extends Model
{
    protected $fillable = [
        'rt_profile_id', 'family_card_number', 'house_number', 'address', 'status', 'registration_type',
        'pendataan_category', 'status_rumah_tinggal', 'kondisi_rumah_milik', 'suku',
    ];

    public function rtProfile(): BelongsTo
    {
        return $this->belongsTo(RtProfile::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function pendataanDocuments(): HasMany
    {
        return $this->hasMany(PendataanDocument::class);
    }

    public function headResident(): HasOne
    {
        return $this->hasOne(Resident::class)
            ->where('is_head_of_family', true);
    }

    public function registrationTypeLabel(): string
    {
        return match ($this->registration_type) {
            'keluarga' => 'Keluarga (beberapa anggota)',
            'perorangan' => 'Perorangan',
            default => '—',
        };
    }

    public function isRtDirectEntry(): bool
    {
        return ! filled($this->pendataan_category);
    }

    public function dataSourceLabel(): string
    {
        return $this->isRtDirectEntry()
            ? 'Entri RT'
            : $this->pendataanCategoryLabel();
    }

    public function pendataanCategoryLabel(): string
    {
        return match ($this->pendataan_category) {
            'warga_pindah' => 'Warga pindah',
            'belum_identitas' => 'Belum punya identitas resmi',
            'pendataan_ulang' => 'Pendataan ulang',
            'warga_baru' => 'Warga baru',
            default => 'Warga baru',
        };
    }

    public function pendataanServicePath(): string
    {
        return match ($this->pendataan_category) {
            'pendataan_ulang' => '/layanan/pendataan-ulang',
            'warga_baru' => '/layanan/pendataan-warga',
            default => '/layanan',
        };
    }

    public function headOfFamily(): ?Resident
    {
        return $this->residents()->where('is_head_of_family', true)->first();
    }

    /** @param  Builder<Household>  $query */
    public function scopeForRtProfile(Builder $query, RtProfile $rt): Builder
    {
        $ids = RtProfile::profileIdsForRtNumber($rt->rt_number);

        return $query->whereIn('rt_profile_id', $ids);
    }

    public function permanentDeletionRequests(): HasMany
    {
        return $this->hasMany(PermanentDeletionRequest::class);
    }

    public function hasPendingDeletionRequest(): bool
    {
        return PermanentDeletionRequest::query()
            ->pending()
            ->where('target_type', 'household')
            ->where('household_id', $this->id)
            ->exists();
    }

    public function latestRejectedDeletionRequest(): ?PermanentDeletionRequest
    {
        return PermanentDeletionRequest::query()
            ->where('target_type', 'household')
            ->where('household_id', $this->id)
            ->where('status', \App\Enums\PermanentDeletionRequestStatus::Rejected)
            ->latest('reviewed_at')
            ->first();
    }

    public function canBePermanentlyDeleted(): bool
    {
        if ($this->hasPendingDeletionRequest()) {
            return false;
        }

        return app(\App\Services\RtResidentDeletionService::class)->canDeleteHousehold($this)['allowed'];
    }

    public function deletionBlockReason(): ?string
    {
        if ($this->hasPendingDeletionRequest()) {
            return 'Pengajuan hapus permanen sedang menunggu persetujuan admin kelurahan.';
        }

        return app(\App\Services\RtResidentDeletionService::class)->canDeleteHousehold($this)['reason'];
    }
}
