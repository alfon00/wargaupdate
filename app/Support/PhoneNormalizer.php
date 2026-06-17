<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class PhoneNormalizer
{
    public static function validationMessage(): string
    {
        return 'Nomor HP harus 11 atau 12 digit angka (format 08… atau 62…).';
    }

    /** @return list<\Illuminate\Contracts\Validation\ValidationRule|string> */
    public static function validationRules(bool $required = false, ?string $unchangedFrom = null): array
    {
        return [
            Rule::requiredIf($required),
            'nullable',
            'string',
            'max:20',
            function (string $attribute, mixed $value, \Closure $fail) use ($unchangedFrom): void {
                $phone = is_string($value) ? $value : null;

                if ($unchangedFrom !== null && (string) $phone === (string) $unchangedFrom) {
                    return;
                }

                if (! self::isValid($phone)) {
                    $fail(self::validationMessage());
                }
            },
        ];
    }

    public static function isValid(?string $phone): bool
    {
        if ($phone === null || trim($phone) === '') {
            return true;
        }

        $digits = preg_replace('/\D/', '', $phone);
        $length = strlen($digits);

        if ($length !== 11 && $length !== 12) {
            return false;
        }

        if (str_starts_with($digits, '0')) {
            return true;
        }

        if (str_starts_with($digits, '62')) {
            return true;
        }

        return false;
    }

    public static function digits(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

    /** @return list<string> */
    public static function variants(?string $phone): array
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if ($digits === '') {
            return [];
        }

        $variants = [$digits];

        if (str_starts_with($digits, '62')) {
            $variants[] = '0'.substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $variants[] = '62'.substr($digits, 1);
        }

        return array_values(array_unique($variants));
    }
}
