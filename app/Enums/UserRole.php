<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Kelurahan = 'kelurahan';
    case KetuaRt = 'ketua_rt';
    case SekretarisRt = 'sekretaris_rt';
    case Warga = 'warga';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Admin Sistem',
            self::Kelurahan => 'Kelurahan',
            self::KetuaRt => 'Ketua RT',
            self::SekretarisRt => 'Sekretaris RT',
            self::Warga => 'Warga',
        };
    }

    public function isRtStaff(): bool
    {
        return in_array($this, [self::KetuaRt, self::SekretarisRt], true);
    }

    public function isKelurahan(): bool
    {
        return $this === self::Kelurahan;
    }

    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function isPengurus(): bool
    {
        return $this->isRtStaff() || $this->isKelurahan() || $this->isSuperAdmin();
    }

    /** @deprecated Use isRtStaff(), isKelurahan(), or isSuperAdmin() */
    public function isStaff(): bool
    {
        return $this->isRtStaff() || $this->isSuperAdmin();
    }

    public function matchesPortal(string $portal): bool
    {
        return match ($portal) {
            'rt' => $this->isRtStaff(),
            'kelurahan' => $this->isKelurahan(),
            'admin' => $this->isSuperAdmin(),
            default => false,
        };
    }
}
