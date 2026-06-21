<?php

namespace App\Support;

final class KelurahanDisplay
{
    public static function forUi(?string $value = null): string
    {
        $name = trim($value ?? config('kelurahan.nama'));
        $stripped = preg_replace('/\b(kelurahan|inauga)\b/iu', '', $name);

        return trim(preg_replace('/\s{2,}/', ' ', $stripped)) ?: '';
    }

    public static function shortName(?string $value = null): string
    {
        return self::forUi($value);
    }
}
