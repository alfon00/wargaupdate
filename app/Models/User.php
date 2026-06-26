<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'resident_id', 'rt_profile_id', 'avatar_path', 'public_bio', 'public_slug'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function rtProfile(): BelongsTo
    {
        return $this->belongsTo(RtProfile::class);
    }

    public function resolvedRtProfile(): ?RtProfile
    {
        if ($this->rt_profile_id) {
            return RtProfile::resolveCanonical($this->rtProfile);
        }

        if (! $this->isRtStaff()) {
            return null;
        }

        $byName = RtProfile::inauga()
            ->where('ketua_rt', $this->name)
            ->first();

        if ($byName) {
            return $byName;
        }

        if (filled($this->phone)) {
            return RtProfile::inauga()
                ->whereHas('users', fn ($q) => $q
                    ->where('role', UserRole::KetuaRt)
                    ->where('phone', $this->phone))
                ->first();
        }

        return null;
    }

    public function isRtStaff(): bool
    {
        return $this->role?->isRtStaff() ?? false;
    }

    public function appearsOnPublicRtProfile(): bool
    {
        return $this->isRtStaff() && $this->rt_profile_id !== null;
    }

    public function isKelurahan(): bool
    {
        return $this->role?->isKelurahan() ?? false;
    }

    public function canManageApplications(): bool
    {
        return $this->isRtStaff();
    }

    public function matchesPortal(string $portal): bool
    {
        return $this->role?->matchesPortal($portal) ?? false;
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            UserRole::KetuaRt => route('rt.dashboard'),
            UserRole::Kelurahan => route('admin.dashboard'),
            default => route('home'),
        };
    }

    public function profileRoute(): string
    {
        return match ($this->role) {
            UserRole::KetuaRt => route('rt.profile'),
            UserRole::Kelurahan => route('admin.profile'),
            default => route('home'),
        };
    }

    public function profileUpdateRoute(): string
    {
        return match ($this->role) {
            UserRole::KetuaRt => route('rt.profile.update'),
            UserRole::Kelurahan => route('admin.profile.update'),
            default => route('home'),
        };
    }

    public function profileAvatarDestroyRoute(): string
    {
        return match ($this->role) {
            UserRole::KetuaRt => route('rt.profile.avatar.destroy'),
            UserRole::Kelurahan => route('admin.profile.avatar.destroy'),
            default => route('home'),
        };
    }

    public function hasUploadedAvatar(): bool
    {
        if (! $this->avatar_path) {
            return false;
        }

        return Storage::disk('public')->exists($this->avatar_path);
    }

    public function avatarUrl(): string
    {
        if ($this->avatar_path && Storage::disk('public')->exists($this->avatar_path)) {
            return Storage::disk('public')->url($this->avatar_path);
        }

        $initial = mb_strtoupper(mb_substr($this->name ?: 'U', 0, 1));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96">'
            .'<rect width="96" height="96" fill="#047857"/>'
            .'<text x="48" y="58" text-anchor="middle" font-size="40" font-family="system-ui,sans-serif" fill="#ecfdf5" font-weight="700">'
            .htmlspecialchars($initial, ENT_XML1)
            .'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
