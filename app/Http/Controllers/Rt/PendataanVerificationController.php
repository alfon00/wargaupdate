<?php

namespace App\Http\Controllers\Rt;

use App\Enums\DomicileStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Rt\Concerns\ResolvesRtProfile;
use App\Jobs\SendPendataanWhatsApp;
use App\Models\NotificationLog;
use App\Models\PendataanDocument;
use App\Models\Resident;
use App\Services\GuestResidentService;
use App\Services\ResidentFaceReferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PendataanVerificationController extends Controller
{
    use ResolvesRtProfile;

    public function __construct(
        private readonly GuestResidentService $guestResidents,
    ) {}

    public function index(): View
    {
        $rt = $this->requireRtProfile();

        $query = Resident::with(['household.rtProfile', 'household.pendataanDocuments'])
            ->forRtProfile($rt)
            ->where('is_head_of_family', true)
            ->pendingPendataan()
            ->latest();

        if ($q = trim((string) request('q', ''))) {
            $term = '%'.$q.'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('nik', 'like', $term);
            });
        }

        $residents = $query->paginate(20)->withQueryString();

        return view('rt.pendataan.index', compact('residents', 'rt'));
    }

    public function show(Resident $resident): View
    {
        abort_unless($resident->is_head_of_family, 404);
        $this->abortUnlessOwnsResident($resident);

        $resident->load(['household.rtProfile', 'household.residents', 'household.pendataanDocuments']);

        $household = $resident->household;
        $orderedMembers = $household
            ? $this->guestResidents->orderedHouseholdResidents($household)
            : collect();

        $documents = $household?->pendataanDocuments ?? collect();
        $kkDocument = $documents->firstWhere('document_type', 'kk');

        $memberDocuments = $orderedMembers->values()->map(function (Resident $member, int $index) use ($documents) {
            $docType = $this->guestResidents->memberIdentityDocumentTypeForResident($member, $index);

            return [
                'member' => $member,
                'document' => $documents->firstWhere('document_type', $docType),
                'index' => $index,
            ];
        });

        $pendataanQuery = [
            'return' => 'pendataan',
            'pendataan_head' => $resident->id,
        ];

        $notificationLogs = NotificationLog::query()
            ->forResidentPendataan($resident->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('rt.pendataan.show', [
            'head' => $resident,
            'members' => $orderedMembers,
            'kkDocument' => $kkDocument,
            'memberDocuments' => $memberDocuments,
            'pendataanQuery' => $pendataanQuery,
            'notificationLogs' => $notificationLogs,
        ]);
    }

    public function viewDocument(Resident $resident, PendataanDocument $document): BinaryFileResponse
    {
        return $this->streamPendataanDocument($resident, $document, inline: true);
    }

    public function downloadDocument(Resident $resident, PendataanDocument $document): StreamedResponse
    {
        return $this->streamPendataanDocument($resident, $document, inline: false);
    }

    protected function streamPendataanDocument(Resident $resident, PendataanDocument $document, bool $inline): BinaryFileResponse|StreamedResponse
    {
        abort_unless($resident->is_head_of_family, 404);
        $this->abortUnlessOwnsResident($resident);
        abort_unless((int) $document->household_id === (int) $resident->household_id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        $filename = $document->original_name ?: 'berkas-'.$document->document_type;

        if ($inline) {
            $mime = $document->mime_type ?: Storage::disk('local')->mimeType($document->file_path);
            $safe = str_replace(['"', "\r", "\n"], '', $filename);

            return response()->file(
                Storage::disk('local')->path($document->file_path),
                [
                    'Content-Type' => $mime ?: 'application/octet-stream',
                    'Content-Disposition' => 'inline; filename="'.$safe.'"',
                ]
            );
        }

        return Storage::disk('local')->download($document->file_path, $filename);
    }

    public function approve(Resident $resident): RedirectResponse
    {
        abort_unless($resident->is_head_of_family, 404);
        $this->abortUnlessOwnsResident($resident);

        $household = $resident->household;
        if (! $household) {
            return back()->withErrors(['error' => 'Data KK tidak ditemukan.']);
        }

        $household->residents()->update([
            'domicile_status' => DomicileStatus::Aktif,
            'verification_notes' => null,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        $household->update(['status' => 'aktif']);

        app(ResidentFaceReferenceService::class)->syncForHousehold($household->fresh(['pendataanDocuments', 'residents']));

        SendPendataanWhatsApp::dispatchSync($resident->id, 'pendataan_verified');

        return redirect()
            ->route('rt.pendataan.index')
            ->with('success', 'Pendataan disetujui. Warga sudah terdata aktif di '.$household->rtProfile?->displayName().'.');
    }

    public function reject(Request $request, Resident $resident): RedirectResponse
    {
        abort_unless($resident->is_head_of_family, 404);
        $this->abortUnlessOwnsResident($resident);

        $validated = $request->validate([
            'rejection_notes' => ['required', 'string', 'max:2000'],
        ]);

        $household = $resident->household;
        if (! $household) {
            return back()->withErrors(['error' => 'Data KK tidak ditemukan.']);
        }

        $household->residents()->update([
            'domicile_status' => DomicileStatus::Aktif,
            'verification_notes' => $validated['rejection_notes'],
            'verified_at' => null,
            'verified_by' => auth()->id(),
        ]);

        $household->update(['status' => 'aktif']);

        SendPendataanWhatsApp::dispatchSync(
            $resident->id,
            'pendataan_rejected',
            $validated['rejection_notes']
        );

        $rejectMessage = match ($household->pendataan_category) {
            'pendataan_ulang' => 'Pendataan ulang ditolak. Warga menerima notifikasi WhatsApp.',
            'warga_baru' => 'Pendataan warga ditolak. Warga menerima notifikasi WhatsApp.',
            default => 'Pendataan ditolak. Warga menerima notifikasi WhatsApp.',
        };

        return redirect()
            ->route('rt.pendataan.index')
            ->with('success', $rejectMessage);
    }
}
