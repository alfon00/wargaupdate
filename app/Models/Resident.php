<?php

namespace App\Models;

use App\Enums\DomicileStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resident extends Model
{
    protected $fillable = [
        'household_id', 'nik', 'name', 'birth_place', 'birth_date', 'gender',
        'religion', 'occupation', 'education', 'marital_status', 'citizenship',
        'relationship_to_head', 'phone', 'is_head_of_family', 'domicile_status', 'whatsapp_notify',
        'verification_notes', 'verified_at', 'verified_by',
        'departed_at', 'departure_reason', 'departure_notes', 'departed_by',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_head_of_family' => 'boolean',
            'whatsapp_notify' => 'boolean',
            'domicile_status' => DomicileStatus::class,
            'verified_at' => 'datetime',
            'departed_at' => 'datetime',
        ];
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function departedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'departed_by');
    }

    public function isDomiciledActive(): bool
    {
        return $this->domicile_status === DomicileStatus::Aktif;
    }

    /** @param  Builder<Resident>  $query */
    public function scopeDomiciledActive(Builder $query): Builder
    {
        return $query->where('domicile_status', DomicileStatus::Aktif);
    }

    /** @param  Builder<Resident>  $query */
    public function scopeDomiciledArchived(Builder $query): Builder
    {
        return $query->whereIn('domicile_status', DomicileStatus::archivedValues());
    }

    public function headOfHousehold(): ?self
    {
        if ($this->is_head_of_family) {
            return $this;
        }

        return $this->household?->residents()->where('is_head_of_family', true)->first();
    }

    public function scopePendingPendataan($query)
    {
        return $query->whereIn('domicile_status', [
            DomicileStatus::MenungguVerifikasi->value,
        ]);
    }

    /** @param  Builder<Resident>  $query */
    public function scopeForRtProfile(Builder $query, RtProfile $rt): Builder
    {
        $ids = RtProfile::profileIdsForRtNumber($rt->rt_number);

        return $query->whereHas('household', fn (Builder $q) => $q->whereIn('rt_profile_id', $ids));
    }

    protected function nik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value,
            set: fn (?string $value) => $value ? preg_replace('/\D/', '', $value) : null,
        );
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function faceReferences(): HasMany
    {
        return $this->hasMany(ResidentFaceReference::class);
    }

    public function suratIdentityVerifications(): HasMany
    {
        return $this->hasMany(SuratIdentityVerification::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function latestNotificationLog(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(NotificationLog::class)->latestOfMany();
    }

    public function hasLatestWhatsappNotificationFailed(): bool
    {
        return $this->whatsapp_notify
            && $this->latestNotificationLog?->status === 'failed';
    }

    public function registeredPhoneForVerification(): ?string
    {
        if (filled($this->phone)) {
            return $this->phone;
        }

        return $this->headOfHousehold()?->phone;
    }

    public function whatsappNotificationPhone(): ?string
    {
        if (! $this->whatsapp_notify) {
            return null;
        }

        if (filled($this->phone)) {
            return $this->phone;
        }

        return $this->headOfHousehold()?->phone;
    }

    public function fullAddress(): string
    {
        $h = $this->household;

        return trim(($h?->address ?? '').' No. '.($h?->house_number ?? ''));
    }

    public function birthPlaceDate(): string
    {
        $place = $this->birth_place ?? '-';
        $date = $this->birth_date?->translatedFormat('d F Y') ?? '-';

        return $place.', '.$date;
    }

    public function permanentDeletionRequests(): HasMany
    {
        return $this->hasMany(PermanentDeletionRequest::class);
    }

    public function hasPendingDeletionRequest(): bool
    {
        return PermanentDeletionRequest::query()
            ->pending()
            ->where('target_type', 'resident')
            ->where('resident_id', $this->id)
            ->exists();
    }

    public function latestRejectedDeletionRequest(): ?PermanentDeletionRequest
    {
        return PermanentDeletionRequest::query()
            ->where('target_type', 'resident')
            ->where('resident_id', $this->id)
            ->where('status', \App\Enums\PermanentDeletionRequestStatus::Rejected)
            ->latest('reviewed_at')
            ->first();
    }

    public function canBePermanentlyDeleted(): bool
    {
        if ($this->hasPendingDeletionRequest()) {
            return false;
        }

        return app(\App\Services\RtResidentDeletionService::class)->canDeleteResident($this)['allowed'];
    }

    public function deletionBlockReason(): ?string
    {
        if ($this->hasPendingDeletionRequest()) {
            return 'Pengajuan hapus permanen sedang menunggu persetujuan admin kelurahan.';
        }

        return app(\App\Services\RtResidentDeletionService::class)->canDeleteResident($this)['reason'];
    }
}
