<?php

namespace App\Services;

class PhoneNormalizer
{
    public static function toWhatsAppChatId(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        if (! str_starts_with($digits, '62')) {
            return null;
        }

        return $digits.'@c.us';
    }

    public static function display(?string $phone): string
    {
        $chatId = self::toWhatsAppChatId($phone);

        return $chatId ? str_replace('@c.us', '', $chatId) : ($phone ?? '-');
    }
}
