<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\View\View;

class LetterVerificationController extends Controller
{
    public function show(string $token): View
    {
        $application = Application::query()
            ->where('letter_verification_token', $token)
            ->whereHas('generatedLetter', fn ($q) => $q->whereNotNull('issued_at'))
            ->with([
                'generatedLetter',
                'serviceType',
                'resident.household.rtProfile',
                'assignedRtProfile',
            ])
            ->firstOrFail();

        $letter = $application->generatedLetter;
        abort_unless($letter !== null, 404);

        return view('public.letter-verify', [
            'letter' => $letter,
            'application' => $application,
        ]);
    }
}
