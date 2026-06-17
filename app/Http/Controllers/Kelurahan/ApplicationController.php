<?php

namespace App\Http\Controllers\Kelurahan;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\RtProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $rtProfiles = RtProfile::forPublicSelect()->get();

        $query = Application::with(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType', 'generatedLetter'])
            ->latest();

        if ($request->filled('rt_profile_id')) {
            $rt = RtProfile::find((int) $request->rt_profile_id);
            if ($rt) {
                $query->forRtProfile($rt);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        if ($request->filled('q')) {
            $term = '%'.$request->q.'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('application_number', 'like', $term)
                    ->orWhereHas('resident', fn ($r) => $r->where('name', 'like', $term))
                    ->orWhere('form_data->archived_applicant->name', 'like', $term);
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        return view('kelurahan.applications.index', compact('applications', 'rtProfiles'));
    }

    public function show(Application $application): View
    {
        $application->load(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType', 'generatedLetter.signer', 'documents']);

        $documents = $application->documents
            ->sortBy(fn (ApplicationDocument $doc) => $doc->document_type)
            ->values();

        $documents->each(fn (ApplicationDocument $d) => $d->setRelation('application', $application));

        $requiredCount = $application->letterSubjectCount();
        if ($requiredCount === 0) {
            $requiredCount = count($application->serviceType->required_fields ?? []);
        }

        return view('kelurahan.applications.show', compact('application', 'documents', 'requiredCount'));
    }

    public function viewDocument(Application $application, ApplicationDocument $document): BinaryFileResponse
    {
        return $this->streamDocument($application, $document, inline: true);
    }

    public function downloadDocument(Application $application, ApplicationDocument $document): StreamedResponse
    {
        return $this->streamDocument($application, $document, inline: false);
    }

    protected function streamDocument(Application $application, ApplicationDocument $document, bool $inline): BinaryFileResponse|StreamedResponse
    {
        abort_unless((int) $document->application_id === (int) $application->id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        $filename = $document->original_name ?: 'lampiran-'.$document->document_type;

        if ($inline) {
            $mime = $document->mime_type ?: Storage::disk('local')->mimeType($document->file_path);

            return response()->file(
                Storage::disk('local')->path($document->file_path),
                $this->inlineFileHeaders($filename, $mime ?: 'application/octet-stream')
            );
        }

        return Storage::disk('local')->download($document->file_path, $filename);
    }

    public function viewLetter(Application $application): BinaryFileResponse
    {
        $letter = $application->generatedLetter;
        abort_unless($letter && Storage::disk('local')->exists($letter->file_path), 404);

        $filename = 'surat-'.$application->application_number.'.pdf';

        return response()->file(
            Storage::disk('local')->path($letter->file_path),
            $this->inlineFileHeaders($filename, 'application/pdf')
        );
    }

    /** @return array<string, string> */
    protected function inlineFileHeaders(string $filename, string $mime): array
    {
        $safe = str_replace(['"', "\r", "\n"], '', $filename);

        return [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$safe.'"',
        ];
    }

    public function downloadLetter(Application $application): StreamedResponse
    {
        $letter = $application->generatedLetter;
        abort_unless($letter && Storage::disk('local')->exists($letter->file_path), 404);

        return Storage::disk('local')->download(
            $letter->file_path,
            'surat-'.$application->application_number.'.pdf'
        );
    }
}
