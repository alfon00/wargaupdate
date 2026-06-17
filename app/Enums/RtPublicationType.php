<?php

namespace App\Enums;

enum RtPublicationType: string
{
    case Kegiatan = 'kegiatan';
    case Pengumuman = 'pengumuman';

    public function label(): string
    {
        return match ($this) {
            self::Kegiatan => 'Kegiatan',
            self::Pengumuman => 'Pengumuman',
        };
    }
}
