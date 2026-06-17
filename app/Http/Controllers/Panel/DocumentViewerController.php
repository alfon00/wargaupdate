<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rt\Concerns\ResolvesRtProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\PendataanDocument;
use App\Models\Resident;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentViewerController extends Controller
{
    use ResolvesRtProfile;

    public function rtLetter(Application $application): View
    {
        $this->abortUnlessOwnsApplication($application);
        $this->abortUnlessLetterExists($application);

        return $this->viewer(
            title: 'Surat '.$application->application_number,
            pdfUrl: route('rt.applications.letter.view', $application),
            downloadUrl: route('rt.applications.download', $application),
            backUrl: $this->rtLetterBackUrl($application),
        );
    }

    public function rtApplicationDocument(Application $application, ApplicationDocument $document): View
    {
        $this->abortUnlessOwnsApplication($application);
        abort_unless((int) $document->application_id === (int) $application->id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return $this->viewer(
            title: $document->typeLabel().' — '.$application->application_number,
            pdfUrl: route('rt.applications.document.view', [$application, $document]),
            downloadUrl: route('rt.applications.document', [$application, $document]),
            backUrl: route('rt.applications.show', $application),
        );
    }

    public function rtPendataanDocument(Resident $resident, PendataanDocument $document): View
    {
        abort_unless($resident->is_head_of_family, 404);
        $this->abortUnlessOwnsResident($resident);
        abort_unless((int) $document->household_id === (int) $resident->household_id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return $this->viewer(
            title: $document->typeLabel().' — '.$resident->name,
            pdfUrl: route('rt.pendataan.document.view', [$resident, $document]),
            downloadUrl: route('rt.pendataan.document.download', [$resident, $document]),
            backUrl: route('rt.pendataan.show', $resident),
        );
    }

    public function kelurahanLetter(Application $application): View
    {
        $this->abortUnlessLetterExists($application);

        return $this->viewer(
            title: 'Surat '.$application->application_number,
            pdfUrl: route('kelurahan.applications.letter.view', $application),
            downloadUrl: route('kelurahan.applications.letter.download', $application),
            backUrl: route('kelurahan.applications.show', $application),
        );
    }

    public function kelurahanApplicationDocument(Application $application, ApplicationDocument $document): View
    {
        abort_unless((int) $document->application_id === (int) $application->id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return $this->viewer(
            title: $document->typeLabel().' — '.$application->application_number,
            pdfUrl: route('kelurahan.applications.document.view', [$application, $document]),
            downloadUrl: route('kelurahan.applications.document', [$application, $document]),
            backUrl: route('kelurahan.applications.show', $application),
        );
    }

    protected function viewer(string $title, string $pdfUrl, ?string $downloadUrl, string $backUrl): View
    {
        return view('panel.document-viewer', compact('title', 'pdfUrl', 'downloadUrl', 'backUrl'));
    }

    protected function abortUnlessLetterExists(Application $application): void
    {
        $letter = $application->generatedLetter;
        abort_unless($letter && Storage::disk('local')->exists($letter->file_path), 404);
    }

    protected function rtLetterBackUrl(Application $application): string
    {
        return route('rt.applications.show', $application);
    }
}
