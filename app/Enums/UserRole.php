<?php

namespace App\Enums;

enum UserRole: string
{
    case Kelurahan = 'kelurahan';
    case KetuaRt = 'ketua_rt';

    public function label(): string
    {
        return match ($this) {
            self::Kelurahan => 'Kelurahan',
            self::KetuaRt => 'Ketua RT',
        };
    }

    /** Grup akun pengurus untuk form admin: RT atau Kelurahan. */
    public function accountGroup(): string
    {
        return match ($this) {
            self::KetuaRt => 'Akun RT',
            self::Kelurahan => 'Akun Kelurahan',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::KetuaRt => 'Mengelola permohonan surat, verifikasi pendataan, dan data warga di satu RT.',
            self::Kelurahan => 'Mengelola akun pengurus, profil RT, layanan, monitoring wilayah, dan konten portal kelurahan.',
        };
    }

    public function panelEyebrow(): string
    {
        return match ($this) {
            self::Kelurahan => 'Panel Kelurahan',
            self::KetuaRt => 'Panel RT',
        };
    }

    public function isRtStaff(): bool
    {
        return $this === self::KetuaRt;
    }

    public function isKelurahan(): bool
    {
        return $this === self::Kelurahan;
    }

    public function isPengurus(): bool
    {
        return $this->isRtStaff() || $this->isKelurahan();
    }

    public function matchesPortal(string $portal): bool
    {
        return match ($portal) {
            'rt' => $this->isRtStaff(),
            'kelurahan', 'admin' => $this->isKelurahan(),
            default => false,
        };
    }

    /** @return list<self> */
    public static function pengurusCases(): array
    {
        return [self::KetuaRt, self::Kelurahan];
    }
}
