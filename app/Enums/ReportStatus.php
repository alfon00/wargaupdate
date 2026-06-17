<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Baru = 'baru';
    case Ditindak = 'ditindak';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Ditindak => 'Sedang ditindak',
            self::Selesai => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Baru => 'lw-badge--blue',
            self::Ditindak => 'lw-badge--amber',
            self::Selesai => 'lw-badge--green',
        };
    }
}
