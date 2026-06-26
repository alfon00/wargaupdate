<?php

namespace App\Support;

use App\Models\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class LetterDownloadLink
{
    public static function signedUrl(Application $application): ?string
    {
        $application->loadMissing('generatedLetter');

        $letter = $application->generatedLetter;

        if (! $letter || ! Storage::disk('local')->exists($letter->file_path)) {
            return null;
        }

        if (! $letter->issued_at) {
            return null;
        }

        return URL::temporarySignedRoute(
            'public.letter.download',
            now()->addDays(90),
            ['application' => $application->id],
        );
    }
}
