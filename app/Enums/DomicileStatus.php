<?php

namespace App\Enums;

enum DomicileStatus: string
{
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Aktif = 'aktif';
    case PerluLengkap = 'perlu_lengkap';
    case PindahKeluar = 'pindah_keluar';
    case Meninggal = 'meninggal';
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return match ($this) {
            self::MenungguVerifikasi => 'Menunggu verifikasi RT',
            self::Aktif => 'Terdata (aktif)',
            self::PerluLengkap => 'Perlu lengkapi berkas',
            self::PindahKeluar => 'Pindah keluar',
            self::Meninggal => 'Meninggal dunia',
            self::Nonaktif => 'Nonaktif (arsip)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::MenungguVerifikasi => 'lw-badge--amber',
            self::Aktif => 'lw-badge',
            self::PerluLengkap => 'lw-badge--amber',
            self::PindahKeluar => 'lw-badge--muted',
            self::Meninggal => 'lw-badge--red',
            self::Nonaktif => 'lw-badge--muted',
        };
    }

    public function isArchived(): bool
    {
        return in_array($this, [self::PindahKeluar, self::Meninggal, self::Nonaktif], true);
    }

    public static function fromDepartureReason(string $reason): self
    {
        return match ($reason) {
            'meninggal' => self::Meninggal,
            'nonaktif' => self::Nonaktif,
            default => self::PindahKeluar,
        };
    }

    /** @return list<string> */
    public static function activeValues(): array
    {
        return [
            self::Aktif->value,
            self::MenungguVerifikasi->value,
            self::PerluLengkap->value,
        ];
    }

    /** @return list<string> */
    public static function archivedValues(): array
    {
        return [
            self::PindahKeluar->value,
            self::Meninggal->value,
            self::Nonaktif->value,
        ];
    }
}
