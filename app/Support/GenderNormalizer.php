<?php

namespace App\Support;

final class GenderNormalizer
{
    /** @return 'L'|'P'|null */
    public static function key(?string $gender): ?string
    {
        if (! $gender) {
            return null;
        }

        $g = mb_strtolower(trim($gender));
        if ($g === 'laki-laki' || $g === 'l' || $g === 'pria') {
            return 'L';
        }
        if ($g === 'perempuan' || $g === 'p' || $g === 'wanita') {
            return 'P';
        }

        return null;
    }
}
