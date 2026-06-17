<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LetterDownloadController extends Controller
{
    public function download(Application $application): StreamedResponse
    {
        $application->loadMissing('generatedLetter');

        $letter = $application->generatedLetter;

        abort_unless($letter && Storage::disk('local')->exists($letter->file_path), 404);
        abort_unless($letter->signature_path || $letter->signed_at, 404);

        $filename = 'surat-'.$application->application_number.'.pdf';

        return response()->streamDownload(
            fn () => print(Storage::disk('local')->get($letter->file_path)),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
