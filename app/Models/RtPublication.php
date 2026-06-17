<?php

namespace App\Models;

use App\Enums\RtPublicationType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RtPublication extends Model
{
    public const PUBLIC_VISIBILITY_DAYS = 30;

    protected $fillable = [
        'rt_profile_id',
        'type',
        'judul',
        'ringkasan',
        'tanggal',
        'lokasi',
        'foto_path',
        'is_published',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => RtPublicationType::class,
            'tanggal' => 'date',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'date',
        ];
    }

    public function rtProfile(): BelongsTo
    {
        return $this->belongsTo(RtProfile::class);
    }

    /** @param  Builder<RtPublication>  $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** @param  Builder<RtPublication>  $query */
    public function scopeKegiatan(Builder $query): Builder
    {
        return $query->where('type', RtPublicationType::Kegiatan);
    }

    /** @param  Builder<RtPublication>  $query */
    public function scopePengumuman(Builder $query): Builder
    {
        return $query->where('type', RtPublicationType::Pengumuman);
    }

    /** Hanya RT yang punya pengurus terdaftar di portal. */
    public function scopeForPublic(Builder $query): Builder
    {
        return $query->whereHas('rtProfile', fn (Builder $q) => $q->withRegisteredStaff());
    }

    /** Pengumuman yang masih boleh tampil di halaman publik. */
    public function scopeVisibleOnPublic(Builder $query): Builder
    {
        $today = Carbon::today('Asia/Jayapura')->toDateString();
        $cutoff = Carbon::now('Asia/Jayapura')->subDays(self::PUBLIC_VISIBILITY_DAYS);

        return $query->where(function (Builder $q) use ($today, $cutoff) {
            $q->where(function (Builder $q2) use ($today) {
                $q2->whereNotNull('expires_at')
                    ->whereDate('expires_at', '>=', $today);
            })->orWhere(function (Builder $q2) use ($cutoff) {
                $q2->whereNull('expires_at')
                    ->where('published_at', '>=', $cutoff);
            });
        });
    }

    public function effectiveExpiresAt(): ?Carbon
    {
        if ($this->expires_at) {
            return $this->expires_at->copy()->timezone('Asia/Jayapura');
        }

        if ($this->published_at) {
            return $this->published_at
                ->copy()
                ->timezone('Asia/Jayapura')
                ->addDays(self::PUBLIC_VISIBILITY_DAYS)
                ->startOfDay();
        }

        return null;
    }

    public function isExpiredOnPublic(): bool
    {
        $effective = $this->effectiveExpiresAt();

        if (! $effective) {
            return false;
        }

        return $effective->copy()->startOfDay()->lt(Carbon::today('Asia/Jayapura'));
    }

    public function fotoUrl(): ?string
    {
        if (! $this->foto_path) {
            return null;
        }

        if (str_starts_with($this->foto_path, 'http://') || str_starts_with($this->foto_path, 'https://')) {
            return $this->foto_path;
        }

        if (Storage::disk('public')->exists($this->foto_path)) {
            return Storage::disk('public')->url($this->foto_path);
        }

        return null;
    }

    public function deleteFoto(): void
    {
        if ($this->foto_path && Storage::disk('public')->exists($this->foto_path)) {
            Storage::disk('public')->delete($this->foto_path);
        }
    }
}
