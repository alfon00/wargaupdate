<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RtProfile extends Model
{
    protected $fillable = [
        'slug', 'rt_number', 'rw_number', 'kelurahan', 'kecamatan', 'kota', 'provinsi',
        'ketua_rt', 'ketua_rw', 'sekretaris_rt', 'alamat_kantor',
        'visi', 'misi', 'phone', 'whatsapp', 'email', 'jam_layanan', 'logo_path',
        'instagram_url', 'facebook_url', 'youtube_url',
    ];

    protected static function booted(): void
    {
        static::saving(function (RtProfile $profile) {
            if (empty($profile->slug) && $profile->rt_number) {
                $profile->slug = 'rt-'.Str::slug($profile->rt_number);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @param  Builder<RtProfile>  $query */
    public function scopeInauga(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('kelurahan', 'like', '%Inauga%')
                ->orWhere('kelurahan', 'like', '%inauga%');
        });
    }

    /** Hanya RT yang sudah punya akun Ketua atau Sekretaris terdaftar di portal. */
    public function scopeWithRegisteredStaff(Builder $query): Builder
    {
        return $query->whereHas('users', fn ($q) => $q
            ->whereIn('role', [UserRole::KetuaRt, UserRole::SekretarisRt])
            ->whereNotNull('rt_profile_id')
            ->whereColumn('users.rt_profile_id', 'rt_profiles.id'));
    }

    /** Profil kanonik per nomor RT (satu id per rt_number). */
    public function scopeCanonicalInauga(Builder $query): Builder
    {
        return $query->inauga()->whereIn('id', static::canonicalProfileIdsForInauga());
    }

    /** Satu baris per nomor RT — id kanonik (pengurus RT > slug/KK > MIN id). */
    public function scopeForPublicSelect(Builder $query): Builder
    {
        return $query->canonicalInauga()->orderBy('rt_number');
    }

    /** @return list<int> */
    public static function canonicalProfileIdsForInauga(): array
    {
        return static::inauga()
            ->pluck('rt_number')
            ->unique()
            ->filter()
            ->map(fn (string $rtNumber) => static::canonicalProfileIdForRtNumber($rtNumber))
            ->filter()
            ->values()
            ->all();
    }

    public static function normalizeRtNumber(?string $rtNumber): string
    {
        $digits = preg_replace('/\D/', '', $rtNumber ?? '') ?: '0';

        return str_pad($digits, 3, '0', STR_PAD_LEFT);
    }

    /** @return list<int> */
    public static function profileIdsForRtNumber(string $rtNumber): array
    {
        $normalized = static::normalizeRtNumber($rtNumber);

        return static::inauga()
            ->get()
            ->filter(fn (self $profile) => static::normalizeRtNumber($profile->rt_number) === $normalized)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public static function canonicalProfileIdForRtNumber(string $rtNumber): ?int
    {
        $normalized = static::normalizeRtNumber($rtNumber);
        $candidates = static::inauga()
            ->get()
            ->filter(fn (self $profile) => static::normalizeRtNumber($profile->rt_number) === $normalized);
        if ($candidates->isEmpty()) {
            return null;
        }

        $candidates = collect($candidates->all());

        $withStaff = $candidates->filter(fn (self $profile) => $profile->hasRegisteredStaff());
        if ($withStaff->isNotEmpty()) {
            return (int) $withStaff->sortBy('id')->first()->id;
        }

        $ranked = $candidates->sort(function (self $a, self $b) {
            $slugA = filled($a->slug) ? 1 : 0;
            $slugB = filled($b->slug) ? 1 : 0;
            if ($slugA !== $slugB) {
                return $slugB <=> $slugA;
            }

            $hhA = $a->households()->count();
            $hhB = $b->households()->count();
            if ($hhA !== $hhB) {
                return $hhB <=> $hhA;
            }

            return $a->id <=> $b->id;
        });

        return (int) $ranked->first()->id;
    }

    public static function resolveCanonical(?self $profile): ?self
    {
        if (! $profile) {
            return null;
        }

        $canonicalId = static::canonicalProfileIdForRtNumber((string) $profile->rt_number);
        if ($canonicalId && $canonicalId !== (int) $profile->id) {
            return static::find($canonicalId) ?? $profile;
        }

        return $profile;
    }

    /** @return list<string> */
    public static function letterKopFieldKeys(): array
    {
        return ['kelurahan', 'kecamatan', 'kota', 'provinsi', 'alamat_kantor'];
    }

    public function letterKopAddressForRtNumber(string $targetRtNumber, ?string $baselineAddress): ?string
    {
        if (! filled($baselineAddress)) {
            return null;
        }

        $from = static::normalizeRtNumber($this->rt_number);
        $to = static::normalizeRtNumber($targetRtNumber);
        if ($from === $to) {
            return $baselineAddress;
        }

        $patterns = [
            '/\bRT[\s\-]*'.preg_quote($from, '/').'\b/i' => 'RT '.$to,
            '/\bRT[\s\-]*'.preg_quote(ltrim($from, '0') ?: '0', '/').'\b/i' => 'RT '.$to,
        ];

        $result = $baselineAddress;
        foreach ($patterns as $pattern => $replacement) {
            $result = preg_replace($pattern, $replacement, $result) ?? $result;
        }

        return $result;
    }

    public function hasRegisteredStaff(): bool
    {
        return $this->users()
            ->whereIn('role', [UserRole::KetuaRt, UserRole::SekretarisRt])
            ->whereNotNull('rt_profile_id')
            ->where('rt_profile_id', $this->id)
            ->exists();
    }

    public static function householdBelongsToRtNumber(int $householdRtProfileId, string $rtNumber): bool
    {
        return in_array($householdRtProfileId, static::profileIdsForRtNumber($rtNumber), true);
    }

    public function households(): HasMany
    {
        return $this->hasMany(Household::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rt_profile_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(RtPublication::class);
    }

    public function citizenReports(): HasMany
    {
        return $this->hasMany(CitizenReport::class);
    }

    public function canBeDeletedByAdmin(): bool
    {
        return $this->deletionBlockReason() === null;
    }

    public function deletionBlockReason(): ?string
    {
        if ($this->relationCount('users') > 0) {
            return 'Profil RT masih memiliki akun pengurus tertaut.';
        }

        if ($this->relationCount('households') > 0) {
            return 'Profil RT masih memiliki data kartu keluarga atau warga.';
        }

        if ($this->relationCount('publications') > 0) {
            return 'Profil RT masih memiliki kegiatan atau pengumuman.';
        }

        if ($this->relationCount('citizenReports') > 0) {
            return 'Profil RT masih memiliki laporan warga.';
        }

        return null;
    }

    private function relationCount(string $relation): int
    {
        $countColumn = $relation.'_count';

        if ($this->relationLoaded($countColumn)) {
            return (int) $this->{$countColumn};
        }

        return $this->{$relation}()->count();
    }

    /** @return Collection<int, User> */
    public function registeredKetuaUsers(): Collection
    {
        return $this->staffUsersCollection()
            ->filter(fn (User $user) => $user->role === UserRole::KetuaRt)
            ->values();
    }

    /** @return Collection<int, User> */
    public function registeredSekretarisUsers(): Collection
    {
        return $this->staffUsersCollection()
            ->filter(fn (User $user) => $user->role === UserRole::SekretarisRt)
            ->values();
    }

    public function registeredStaffCount(): int
    {
        return $this->registeredKetuaUsers()->count() + $this->registeredSekretarisUsers()->count();
    }

    public function primaryKetua(): ?User
    {
        return $this->registeredKetuaUsers()->first();
    }

    public function primarySekretaris(): ?User
    {
        return $this->registeredSekretarisUsers()->first();
    }

    public function publicLeadName(): string
    {
        return $this->primaryKetua()?->name ?? $this->displayName();
    }

    public function publicLeadPhotoUrl(): ?string
    {
        $ketua = $this->primaryKetua();
        if (! $ketua || ! $ketua->avatar_path || ! Storage::disk('public')->exists($ketua->avatar_path)) {
            return null;
        }

        return $ketua->avatarUrl();
    }

    public function activeResidentCount(): int
    {
        return Resident::forRtProfile($this)->domiciledActive()->count();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, self>  $profiles
     * @return array<int, int> keyed by profile id
     */
    public static function activeResidentCountsForProfiles(Collection $profiles): array
    {
        $countsByProfileId = $profiles->mapWithKeys(fn (self $profile) => [$profile->id => 0])->all();

        if ($profiles->isEmpty()) {
            return $countsByProfileId;
        }

        $allProfileIds = $profiles
            ->flatMap(fn (self $profile) => static::profileIdsForRtNumber((string) $profile->rt_number))
            ->unique()
            ->values()
            ->all();

        if ($allProfileIds === []) {
            return $countsByProfileId;
        }

        $countsByRtProfileId = Resident::query()
            ->domiciledActive()
            ->join('households', 'residents.household_id', '=', 'households.id')
            ->whereIn('households.rt_profile_id', $allProfileIds)
            ->groupBy('households.rt_profile_id')
            ->select('households.rt_profile_id', DB::raw('count(*) as resident_count'))
            ->pluck('resident_count', 'rt_profile_id');

        foreach ($profiles as $profile) {
            $total = 0;
            foreach (static::profileIdsForRtNumber((string) $profile->rt_number) as $profileId) {
                $total += (int) ($countsByRtProfileId[$profileId] ?? 0);
            }
            $countsByProfileId[$profile->id] = $total;
        }

        return $countsByProfileId;
    }

    public function publicContactPhone(): ?string
    {
        return $this->primaryKetua()?->phone
            ?? $this->primarySekretaris()?->phone
            ?? $this->phone;
    }

    public function publicContactEmail(): ?string
    {
        if (! filled($this->email)) {
            return null;
        }

        $staffEmails = $this->staffUsersCollection()
            ->pluck('email')
            ->filter()
            ->map(fn (string $email) => Str::lower(trim($email)))
            ->all();

        if (in_array(Str::lower(trim($this->email)), $staffEmails, true)) {
            return null;
        }

        return $this->email;
    }

    public function hasExpandablePublicDetail(): bool
    {
        $sekretaris = $this->registeredSekretarisUsers();

        if ($sekretaris->isEmpty()) {
            return false;
        }

        if ($sekretaris->count() > 1) {
            return true;
        }

        return filled($sekretaris->first()?->phone);
    }

    /** @return Collection<int, User> */
    private function staffUsersCollection(): Collection
    {
        $filter = fn (User $user) => $user->appearsOnPublicRtProfile()
            && (int) $user->rt_profile_id === (int) $this->id;

        if ($this->relationLoaded('users')) {
            return $this->users->filter($filter)->sortBy('id')->values();
        }

        return $this->users()
            ->whereIn('role', [UserRole::KetuaRt, UserRole::SekretarisRt])
            ->whereNotNull('rt_profile_id')
            ->where('rt_profile_id', $this->id)
            ->orderBy('id')
            ->get();
    }

    public function displayName(): string
    {
        return 'RT '.($this->rt_number ?: '—');
    }

    public function rtLabelNumber(): string
    {
        return $this->rt_number ?: '—';
    }

    /** Foto hanya dari unggahan akun pengurus RT, bukan gambar statis images/rt. */
    public function hasUploadedPhoto(): bool
    {
        if (! $this->logo_path) {
            return false;
        }

        if (str_starts_with($this->logo_path, 'images/')) {
            return false;
        }

        if (str_starts_with($this->logo_path, 'http')) {
            return true;
        }

        $local = ltrim($this->logo_path, '/');
        if (str_starts_with($local, 'storage/')) {
            return true;
        }

        return Storage::disk('public')->exists($this->logo_path);
    }

    /** URL foto untuk halaman /profil — dari akun Ketua RT terdaftar. */
    public function publicPhotoUrl(): ?string
    {
        return $this->publicLeadPhotoUrl();
    }

    /** Fallback gambar statis — untuk keperluan non-publik jika diperlukan. */
    public function photoUrl(): string
    {
        return $this->publicPhotoUrl() ?? $this->staticPhotoUrl();
    }

    private function staticPhotoUrl(): string
    {
        $rt = preg_replace('/\D/', '', $this->rt_number ?? '') ?: '000';
        $candidates = [
            "images/rt/rt-{$rt}.webp",
            "images/rt/rt-{$rt}.jpg",
            "images/rt/rt-{$rt}.png",
            "images/rt/rt-{$rt}.svg",
            "images/rt/{$this->slug}.webp",
            "images/rt/{$this->slug}.jpg",
            "images/rt/{$this->slug}.svg",
        ];

        foreach ($candidates as $path) {
            if (is_file(public_path($path))) {
                return asset($path);
            }
        }

        return asset('images/rt/placeholder.svg');
    }

    public static function forRtStaffUser(User $user): ?self
    {
        return $user->resolvedRtProfile();
    }
}
