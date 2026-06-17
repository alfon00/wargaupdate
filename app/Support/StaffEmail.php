<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class StaffEmail
{
    public static function domain(): string
    {
        return (string) config('kelurahan.staff_email_domain', 'layananwarga.my.id');
    }

    public static function suffix(): string
    {
        return '@'.self::domain();
    }

    public static function compose(string $local): string
    {
        $normalized = self::normalizeLocalPart($local);

        return $normalized.'@'.self::domain();
    }

    public static function localPartForForm(?string $email): string
    {
        if ($email === null || trim($email) === '') {
            return '';
        }

        $email = strtolower(trim($email));
        $suffix = self::suffix();

        if (str_ends_with($email, $suffix)) {
            return substr($email, 0, -strlen($suffix));
        }

        $at = strpos($email, '@');

        return $at !== false ? substr($email, 0, $at) : $email;
    }

    public static function normalizeLocalPart(string $local): string
    {
        return strtolower(trim($local));
    }

    public static function isValidLocalPart(?string $local): bool
    {
        if ($local === null || trim($local) === '') {
            return false;
        }

        $normalized = self::normalizeLocalPart($local);
        $maxLocal = 64 - strlen(self::domain()) - 1;

        if ($maxLocal < 1 || strlen($normalized) > $maxLocal) {
            return false;
        }

        return (bool) preg_match('/^[a-z0-9._-]+$/', $normalized);
    }

    public static function validationMessage(): string
    {
        return 'Bagian email hanya huruf kecil, angka, titik, strip, dan garis bawah.';
    }

    /** @return list<\Illuminate\Contracts\Validation\ValidationRule|string> */
    public static function validationRules(bool $required = true): array
    {
        return [
            Rule::requiredIf($required),
            'string',
            'max:64',
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || ! self::isValidLocalPart($value)) {
                    $fail(self::validationMessage());
                }
            },
        ];
    }
}
