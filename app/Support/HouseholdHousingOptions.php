<?php

namespace App\Support;

final class HouseholdHousingOptions
{
    public const STATUS_MILIK_SENDIRI = 'Milik sendiri';

    public const STATUS_KONTRAK = 'Kontrak';

    public const STATUS_MENUMPANG = 'Menumpang';

    public const KONDISI_LAYAK = 'layak';

    public const KONDISI_TIDAK_LAYAK = 'tidak_layak';

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_MILIK_SENDIRI => self::STATUS_MILIK_SENDIRI,
            self::STATUS_KONTRAK => self::STATUS_KONTRAK,
            self::STATUS_MENUMPANG => self::STATUS_MENUMPANG,
        ];
    }

    /** @return list<string> */
    public static function statusValues(): array
    {
        return array_keys(self::statusOptions());
    }

    /** @return array<string, string> */
    public static function kondisiOptions(): array
    {
        return [
            self::KONDISI_LAYAK => 'Layak',
            self::KONDISI_TIDAK_LAYAK => 'Tidak layak',
        ];
    }

    /** @return list<string> */
    public static function kondisiValues(): array
    {
        return array_keys(self::kondisiOptions());
    }

    public static function requiresKondisiRumahMilik(?string $statusRumahTinggal): bool
    {
        return self::normalizeStatus($statusRumahTinggal) === self::STATUS_MILIK_SENDIRI;
    }

    public static function normalizeStatus(?string $status): ?string
    {
        if (! filled($status)) {
            return null;
        }

        $trimmed = trim($status);
        foreach (self::statusOptions() as $canonical) {
            if (mb_strtolower($trimmed) === mb_strtolower($canonical)) {
                return $canonical;
            }
        }

        return $trimmed;
    }

    public static function statusLabel(?string $status): string
    {
        $normalized = self::normalizeStatus($status);

        if ($normalized === null) {
            return '—';
        }

        return self::statusOptions()[$normalized] ?? $normalized;
    }

    public static function kondisiLabel(?string $value): string
    {
        return self::kondisiOptions()[$value] ?? '—';
    }
}
