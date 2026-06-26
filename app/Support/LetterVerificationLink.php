<?php

namespace App\Support;

use App\Models\Application;
use Illuminate\Support\Str;

class LetterVerificationLink
{
    public static function resolveToken(Application $application): string
    {
        $application->loadMissing('generatedLetter');

        if (filled($application->letter_verification_token)) {
            return $application->letter_verification_token;
        }

        $fromLetter = $application->generatedLetter?->verification_token;
        if (filled($fromLetter)) {
            $application->forceFill(['letter_verification_token' => $fromLetter])->saveQuietly();

            return $fromLetter;
        }

        $token = (string) Str::uuid();
        $application->forceFill(['letter_verification_token' => $token])->saveQuietly();

        return $token;
    }

    public static function urlForToken(string $token): string
    {
        return route('public.letter.verify', ['token' => $token], absolute: true);
    }

    public static function url(Application $application): ?string
    {
        $application->loadMissing('generatedLetter');

        if (! $application->generatedLetter?->issued_at) {
            return null;
        }

        $token = self::resolveToken($application);

        return self::urlForToken($token);
    }
}
