<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    protected $fillable = [
        'application_number', 'service_type_id', 'resident_id', 'rt_profile_id', 'submitted_by',
        'status', 'purpose', 'form_data', 'rejection_reason', 'processed_by',
        'submitted_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'form_data' => 'array',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function assignedRtProfile(): BelongsTo
    {
        return $this->belongsTo(RtProfile::class, 'rt_profile_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function generatedLetter(): HasOne
    {
        return $this->hasOne(GeneratedLetter::class);
    }

    public function suratIdentityVerification(): HasOne
    {
        return $this->hasOne(SuratIdentityVerification::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public static function generateNumber(?string $rtNumber = null): string
    {
        $rt = RtProfile::normalizeRtNumber($rtNumber);
        $prefix = 'RT'.$rt.'-'.now()->format('Ym');
        $last = static::where('application_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('application_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function getRouteKeyName(): string
    {
        return 'application_number';
    }

    public function resolvedRtProfile(): ?RtProfile
    {
        if ($this->rt_profile_id) {
            return RtProfile::resolveCanonical($this->assignedRtProfile);
        }

        return RtProfile::resolveCanonical($this->resident?->household?->rtProfile);
    }

    /** @return array<string, mixed>|null */
    public function applicantSnapshot(): ?array
    {
        $snapshot = $this->form_data['archived_applicant'] ?? null;

        return is_array($snapshot) ? $snapshot : null;
    }

    public function hasDetachedApplicant(): bool
    {
        return $this->resident_id === null;
    }

    public function applicantName(): string
    {
        if ($this->resident) {
            return $this->resident->name;
        }

        return $this->applicantSnapshot()['name'] ?? 'Warga (data dihapus)';
    }

    public function applicantNik(): ?string
    {
        return $this->resident?->nik ?? $this->applicantSnapshot()['nik'] ?? null;
    }

    public function applicantPhone(): ?string
    {
        return $this->resident?->phone ?? $this->applicantSnapshot()['phone'] ?? null;
    }

    public function applicantRtLabel(): string
    {
        if ($this->resident) {
            $label = $this->resident->household?->rtProfile?->displayName();
            if ($label) {
                return $label;
            }
        } else {
            $snapshotLabel = $this->applicantSnapshot()['rt_label'] ?? null;
            if ($snapshotLabel) {
                return $snapshotLabel;
            }
        }

        return $this->assignedRtProfile?->displayName() ?? '—';
    }

    public function applicantAddressWithHouseNumber(bool $includeHouseNumber = false): string
    {
        if ($this->resident) {
            $address = $this->resident->household?->address ?: '—';

            if ($includeHouseNumber && $this->resident->household?->house_number) {
                return $address.' No. '.$this->resident->household->house_number;
            }

            return $address;
        }

        $snapshot = $this->applicantSnapshot();
        $address = $snapshot['address'] ?? '—';

        if ($includeHouseNumber && filled($snapshot['house_number'] ?? null)) {
            return $address.' No. '.$snapshot['house_number'];
        }

        return $address;
    }

    public function applicantBirthPlaceDate(): string
    {
        if ($this->resident) {
            return $this->resident->birthPlaceDate();
        }

        $snapshot = $this->applicantSnapshot();
        if (! $snapshot) {
            return '—';
        }

        $place = $snapshot['birth_place'] ?? '-';
        $date = filled($snapshot['birth_date'] ?? null)
            ? \Illuminate\Support\Carbon::parse($snapshot['birth_date'])->translatedFormat('d F Y')
            : '-';

        return $place.', '.$date;
    }

    public function applicantGender(): ?string
    {
        return $this->resident?->gender ?? $this->applicantSnapshot()['gender'] ?? null;
    }

    public function applicantOccupation(): ?string
    {
        return $this->resident?->occupation ?? $this->applicantSnapshot()['occupation'] ?? null;
    }

    public function applicantReligion(): ?string
    {
        return $this->resident?->religion ?? $this->applicantSnapshot()['religion'] ?? null;
    }

    public function applicantMaritalStatus(): ?string
    {
        return $this->resident?->marital_status ?? $this->applicantSnapshot()['marital_status'] ?? null;
    }

    public function applicantCitizenship(): ?string
    {
        return $this->resident?->citizenship ?? $this->applicantSnapshot()['citizenship'] ?? null;
    }

    /** @return list<array{name: string, nik: string, resident_id?: int|null}> */
    public function letterSubjects(): array
    {
        $subjects = $this->form_data['letter_subjects'] ?? [];

        return is_array($subjects) ? array_values($subjects) : [];
    }

    public function letterSubjectCount(): int
    {
        $count = (int) ($this->form_data['subject_count'] ?? 0);

        if ($count > 0) {
            return $count;
        }

        return count($this->letterSubjects());
    }

    /** @return array<string, mixed> */
    public static function buildApplicantSnapshot(Resident $resident): array
    {
        $resident->loadMissing('household.rtProfile');

        return [
            'name' => $resident->name,
            'nik' => $resident->nik,
            'phone' => $resident->phone,
            'gender' => $resident->gender,
            'birth_place' => $resident->birth_place,
            'birth_date' => $resident->birth_date?->format('Y-m-d'),
            'occupation' => $resident->occupation,
            'religion' => $resident->religion,
            'marital_status' => $resident->marital_status,
            'citizenship' => $resident->citizenship,
            'rt_label' => $resident->household?->rtProfile?->displayName(),
            'address' => $resident->household?->address,
            'house_number' => $resident->household?->house_number,
            'archived_at' => now()->toIso8601String(),
        ];
    }

    public function archiveApplicantFromResident(Resident $resident): void
    {
        $formData = $this->form_data ?? [];

        if (! isset($formData['archived_applicant'])) {
            $formData['archived_applicant'] = static::buildApplicantSnapshot($resident);
        }

        $this->forceFill([
            'form_data' => $formData,
            'resident_id' => null,
        ])->save();
    }

    public function hasSignedPublishedLetter(): bool
    {
        $letter = $this->generatedLetter;

        if (! $letter) {
            return false;
        }

        return (bool) ($letter->signature_path || $letter->signed_at);
    }

    /** @return array{number?: string, issued_at?: string, issued_by?: int}|null */
    public function manualLetter(): ?array
    {
        $manual = $this->form_data['manual_letter'] ?? null;

        return is_array($manual) ? $manual : null;
    }

    public function manualLetterNumber(): ?string
    {
        $number = $this->manualLetter()['number'] ?? null;

        return is_string($number) && $number !== '' ? $number : null;
    }

    public function hasManualLetterIssued(): bool
    {
        return $this->manualLetterNumber() !== null;
    }

    public function issuedLetterNumber(): ?string
    {
        return $this->generatedLetter?->letter_number ?? $this->manualLetterNumber();
    }

    public function hasIssuedLetter(): bool
    {
        return $this->hasManualLetterIssued() || $this->generatedLetter !== null;
    }

    public function issuedLetterSource(): ?string
    {
        if ($this->hasManualLetterIssued()) {
            return 'manual';
        }

        if ($this->generatedLetter) {
            return 'pdf';
        }

        return null;
    }

    /** Permohonan yang masih perlu perhatian RT (badge sidebar & dashboard). */
    public function scopePendingRtSidebar(Builder $query): Builder
    {
        return $query->whereIn('status', [
            ApplicationStatus::Diajukan,
            ApplicationStatus::VerifikasiRt,
        ]);
    }

    /** @param  Builder<Application>  $query */
    public function scopeForRtProfile(Builder $query, RtProfile $rt): Builder
    {
        $ids = RtProfile::profileIdsForRtNumber($rt->rt_number);

        return $query->where(function (Builder $q) use ($ids) {
            $q->whereIn('rt_profile_id', $ids)
                ->orWhereHas('resident.household', fn (Builder $household) => $household->whereIn('rt_profile_id', $ids));
        });
    }
}
