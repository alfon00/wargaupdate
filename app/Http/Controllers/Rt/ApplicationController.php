<?php

namespace App\Http\Controllers\Rt;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Rt\Concerns\ResolvesRtProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\NotificationLog;
use App\Models\Resident;
use App\Models\SuratIdentityVerification;
use App\Services\ApplicationDeletionService;
use App\Services\LetterGeneratorService;
use App\Services\WahaNotificationService;
use App\Support\ApplicationRejectionMessage;
use App\Support\LetterFieldSchema;
use App\Support\ResidentLetterProfile;
use App\Support\SignatureStorage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    use ResolvesRtProfile;

    public function __construct(
        protected LetterGeneratorService $letters,
        protected WahaNotificationService $waha,
        protected ApplicationDeletionService $applicationDeletion,
    ) {}

    public function index(): View
    {
        $rt = $this->requireRtProfile();

        $query = Application::with(['resident.household.rtProfile', 'serviceType'])
            ->forRtProfile($rt)
            ->latest();

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($q = trim((string) request('q', ''))) {
            $term = '%'.$q.'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('application_number', 'like', $term)
                    ->orWhereHas('resident', fn ($r) => $r->where('name', 'like', $term))
                    ->orWhere('form_data->archived_applicant->name', 'like', $term);
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        return view('rt.applications.index', compact('applications', 'rt'));
    }

    public function destroy(Application $application): RedirectResponse
    {
        $this->abortUnlessOwnsApplication($application);

        $number = $application->application_number;

        $this->applicationDeletion->delete($application);

        return redirect()
            ->route('rt.applications.index', request()->only(['q', 'status']))
            ->with('success', "Permohonan {$number} berhasil dihapus.");
    }

    public function show(Application $application): View
    {
        $this->abortUnlessOwnsApplication($application);
        $application->load(['resident.household.rtProfile', 'serviceType', 'generatedLetter', 'documents', 'suratIdentityVerification']);

        $documents = $application->documents
            ->sortBy(fn (ApplicationDocument $doc) => $doc->document_type)
            ->values();

        $requiredCount = $application->letterSubjectCount();
        if ($requiredCount === 0) {
            $requiredCount = count($application->serviceType->required_fields ?? []);
        }
        $rejectMessageTemplate = ApplicationRejectionMessage::template($application);
        $notificationLogs = NotificationLog::query()
            ->where('application_id', $application->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('rt.applications.show', compact(
            'application',
            'documents',
            'requiredCount',
            'rejectMessageTemplate',
            'notificationLogs',
        ));
    }

    public function verify(Application $application): RedirectResponse
    {
        $this->abortUnlessOwnsApplication($application);

        if (! $application->status->canBeReviewedByRt()) {
            return back()->withErrors(['error' => 'Permohonan ini tidak dapat diverifikasi pada status saat ini.']);
        }

        $application->update([
            'status' => ApplicationStatus::VerifikasiRt,
            'processed_by' => auth()->id(),
            'rejection_reason' => null,
            'completed_at' => null,
        ]);

        return redirect()
            ->route('rt.applications.letter.compose', $application)
            ->with('success', 'Permohonan diterima. Lanjutkan susun dan tandatangani surat pengantar RT.');
    }

    /** @deprecated Use verify() — kept for backward-compatible route name */
    public function approve(Application $application): RedirectResponse
    {
        return $this->verify($application);
    }

    public function reject(Request $request, Application $application): RedirectResponse
    {
        $this->abortUnlessOwnsApplication($application);

        if (! $application->status->canRejectByRt()) {
            return back()->withErrors(['error' => 'Permohonan ini tidak dapat ditolak pada status saat ini.']);
        }

        $validated = $request->validate([
            'rejection_message' => ['required', 'string', 'max:2000'],
        ]);

        $number = $application->application_number;
        $this->waha->notifyApplicationRejected($application, $validated['rejection_message']);

        $this->applicationDeletion->delete($application);

        return redirect()
            ->route('rt.applications.index', request()->only(['q', 'status']))
            ->with('success', "Permohonan {$number} ditolak dan dihapus. Warga menerima notifikasi WhatsApp.");
    }

    public function markReady(Application $application): RedirectResponse
    {
        $this->abortUnlessOwnsApplication($application);

        if (! $application->status->canMarkReady()) {
            return back()->withErrors(['error' => 'Status permohonan tidak dapat ditandai siap diambil.']);
        }

        $application->updateQuietly([
            'status' => ApplicationStatus::SiapDiambil,
            'processed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Permohonan ditandai siap diambil.');
    }

    public function updateStatus(Request $request, Application $application): RedirectResponse
    {
        $this->abortUnlessOwnsApplication($application);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update([
            'status' => $validated['status'],
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'processed_by' => auth()->id(),
            'completed_at' => in_array($validated['status'], [
                ApplicationStatus::Disetujui->value,
                ApplicationStatus::Ditolak->value,
                ApplicationStatus::SiapDiambil->value,
            ], true) ? now() : $application->completed_at,
        ]);

        return back()->with('success', 'Status permohonan diperbarui.');
    }

    public function viewDocument(Application $application, ApplicationDocument $document): BinaryFileResponse
    {
        return $this->streamDocument($application, $document, inline: true);
    }

    public function downloadDocument(Application $application, ApplicationDocument $document): StreamedResponse
    {
        return $this->streamDocument($application, $document, inline: false);
    }

    public function viewIdentitySelfie(Application $application): BinaryFileResponse
    {
        $this->abortUnlessOwnsApplication($application);

        $verification = SuratIdentityVerification::query()
            ->where('application_id', $application->id)
            ->first();

        abort_unless($verification && $verification->selfieExists(), 404);

        $path = Storage::disk('local')->path($verification->selfie_path);
        $mime = Storage::disk('local')->mimeType($verification->selfie_path) ?: 'image/jpeg';

        return response()->file($path, $this->inlineFileHeaders('selfie-verifikasi.jpg', $mime));
    }

    protected function streamDocument(Application $application, ApplicationDocument $document, bool $inline): BinaryFileResponse|StreamedResponse
    {
        $this->abortUnlessOwnsApplication($application);
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

    public function composeLetter(Application $application): View|RedirectResponse
    {
        $this->abortUnlessOwnsApplication($application);

        if (! $application->status->canGenerateLetter()) {
            return redirect()
                ->route('rt.applications.show', $application)
                ->withErrors(['letter' => 'Verifikasi berkas terlebih dahulu sebelum menyusun surat.']);
        }

        $application->load(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType', 'generatedLetter']);

        $fieldSchema = LetterFieldSchema::forServiceCode($application->serviceType->code);
        $fieldValues = LetterFieldSchema::defaultValues($application);
        $rtProfile = $application->resolvedRtProfile();

        $kopProfileIncomplete = false;
        $missingKopLabels = [];
        if ($rtProfile) {
            $kopFieldLabels = [
                'kelurahan' => 'Kelurahan',
                'kecamatan' => 'Kecamatan/distrik',
                'alamat_kantor' => 'Alamat kantor RT',
            ];
            foreach ($kopFieldLabels as $key => $label) {
                if (! filled($rtProfile->{$key})) {
                    $missingKopLabels[] = $label;
                }
            }
            $kopProfileIncomplete = $missingKopLabels !== [];
        }

        $publishedLetter = $application->generatedLetter;
        $hasPublishedPdf = $publishedLetter
            && Storage::disk('local')->exists($publishedLetter->file_path);

        $resident = $application->resident;
        $profileIncomplete = $resident && ! ResidentLetterProfile::isComplete($resident);
        $missingProfileLabels = $resident ? ResidentLetterProfile::missingLabels($resident) : [];

        $existingSignatureDataUri = null;
        if ($publishedLetter?->signature_path) {
            $existingSignatureDataUri = SignatureStorage::toDataUriFromPath($publishedLetter->signature_path);
        }

        if (! $existingSignatureDataUri) {
            $draftSignature = $application->form_data['letter']['signature_data'] ?? null;
            if ($draftSignature && ! SignatureStorage::isBlank($draftSignature)) {
                $existingSignatureDataUri = $draftSignature;
            }
        }

        $canSendLetterWhatsApp = false;
        $letterWhatsAppBlockReason = null;

        if ($hasPublishedPdf && $publishedLetter) {
            if (! $publishedLetter->signature_path && ! $publishedLetter->signed_at) {
                $letterWhatsAppBlockReason = 'Gambar tanda tangan Ketua RT terlebih dahulu.';
            } elseif (! filled($resident?->whatsappNotificationPhone())) {
                $letterWhatsAppBlockReason = 'Nomor HP warga belum terdaftar.';
            } elseif (! $resident->whatsapp_notify) {
                $letterWhatsAppBlockReason = 'Notifikasi WhatsApp warga nonaktif.';
            } else {
                $canSendLetterWhatsApp = true;
            }
        }

        $lastLetterWhatsAppLog = NotificationLog::query()
            ->where('application_id', $application->id)
            ->where('event', 'letter_sent')
            ->latest()
            ->first();

        return view('rt.applications.letter-compose', compact(
            'application',
            'fieldSchema',
            'fieldValues',
            'rtProfile',
            'publishedLetter',
            'hasPublishedPdf',
            'profileIncomplete',
            'missingProfileLabels',
            'kopProfileIncomplete',
            'missingKopLabels',
            'existingSignatureDataUri',
            'canSendLetterWhatsApp',
            'letterWhatsAppBlockReason',
            'lastLetterWhatsAppLog',
        ));
    }

    public function lookupLetterResident(Request $request, Application $application): JsonResponse
    {
        $this->abortUnlessOwnsApplication($application);

        if (! $application->status->canGenerateLetter()) {
            abort(403, 'Verifikasi berkas terlebih dahulu sebelum menyusun surat.');
        }

        $rt = $application->resolvedRtProfile();
        if (! $rt) {
            return response()->json(['message' => 'Profil RT tidak ditemukan.'], 404);
        }

        $residentId = (int) $request->query('resident_id', 0);
        if ($residentId > 0) {
            $resident = Resident::query()->forRtProfile($rt)->find($residentId);
            if (! $resident) {
                return response()->json(['message' => 'Warga tidak ditemukan di data RT ini.'], 404);
            }

            return response()->json([
                'ok' => true,
                'fields' => LetterFieldSchema::valuesFromResident($resident, $application),
            ]);
        }

        $nik = preg_replace('/\D/', '', (string) $request->query('nik', '')) ?? '';
        if (strlen($nik) === 16) {
            $resident = Resident::query()->forRtProfile($rt)->where('nik', $nik)->first();
            if (! $resident) {
                return response()->json(['message' => 'NIK tidak ditemukan di data warga RT.'], 404);
            }

            return response()->json([
                'ok' => true,
                'fields' => LetterFieldSchema::valuesFromResident($resident, $application),
            ]);
        }

        $name = trim((string) $request->query('name', ''));
        if (mb_strlen($name) >= 3) {
            $matches = Resident::query()
                ->forRtProfile($rt)
                ->where('name', 'like', '%'.$name.'%')
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'name', 'nik']);

            if ($matches->isEmpty()) {
                return response()->json(['message' => 'Nama tidak ditemukan di data warga RT.'], 404);
            }

            if ($matches->count() === 1) {
                return response()->json([
                    'ok' => true,
                    'fields' => LetterFieldSchema::valuesFromResident($matches->first(), $application),
                ]);
            }

            return response()->json([
                'ok' => true,
                'choices' => $matches->map(static fn (Resident $resident): array => [
                    'id' => $resident->id,
                    'name' => $resident->name,
                    'nik' => $resident->nik,
                ])->values(),
            ]);
        }

        return response()->json(['message' => 'Isi NIK (16 digit) atau nama (min. 3 huruf).'], 422);
    }

    public function sendLetterWhatsApp(Application $application): RedirectResponse
    {
        $this->abortUnlessOwnsApplication($application);

        if (! $application->status->canGenerateLetter()) {
            return back()->withErrors(['letter' => 'Status permohonan tidak memungkinkan pengiriman surat.']);
        }

        $log = $this->waha->sendLetterPdf($application);

        return match ($log->status) {
            'sent' => redirect()
                ->route('rt.applications.letter.compose', $application)
                ->with('success', 'Surat PDF berhasil dikirim ke WhatsApp warga.'),
            'skipped' => redirect()
                ->route('rt.applications.letter.compose', $application)
                ->withErrors(['letter' => $log->error_message ?? 'Pengiriman WhatsApp dilewati.']),
            default => redirect()
                ->route('rt.applications.letter.compose', $application)
                ->withErrors(['letter' => $log->error_message ?? 'Gagal mengirim surat via WhatsApp.']),
        };
    }

    public function saveLetterDraft(Request $request, Application $application): RedirectResponse
    {
        $this->abortUnlessOwnsApplication($application);

        if (! $application->status->canGenerateLetter()) {
            return back()->withErrors(['letter' => 'Status permohonan tidak memungkinkan penyimpanan draf surat.']);
        }

        $validated = $request->validate([
            'fields' => ['required', 'array'],
            'signature_data' => ['nullable', 'string', 'max:500000'],
            ...LetterFieldSchema::validate($application->serviceType->code, $request->input('fields', [])),
            ...LetterFieldSchema::letterNumberValidationRules(required: false),
        ]);

        $letterDraft = array_merge($application->form_data['letter'] ?? [], [
            'fields' => $validated['fields'],
            'draft_saved_at' => now()->toIso8601String(),
            'draft_saved_by' => auth()->id(),
        ]);

        if (array_key_exists('signature_data', $validated)) {
            $letterDraft['signature_data'] = $validated['signature_data'];
        }

        $formData = $application->form_data ?? [];
        $formData['letter'] = $letterDraft;
        $application->update(['form_data' => $formData]);

        return redirect()
            ->route('rt.applications.letter.compose', $application)
            ->with('success', 'Draf data surat disimpan.');
    }

    public function saveLetterSignature(Request $request, Application $application): JsonResponse
    {
        $this->abortUnlessOwnsApplication($application);

        if (! $application->status->canGenerateLetter()) {
            return response()->json(['message' => 'Status permohonan tidak memungkinkan penyimpanan tanda tangan.'], 403);
        }

        $validated = $request->validate([
            'signature_data' => ['nullable', 'string', 'max:500000'],
        ]);

        $signatureData = $validated['signature_data'] ?? null;
        if (SignatureStorage::isBlank($signatureData)) {
            $signatureData = null;
        }

        $formData = $application->form_data ?? [];
        $formData['letter'] = array_merge($formData['letter'] ?? [], [
            'signature_data' => $signatureData,
            'signature_saved_at' => now()->toIso8601String(),
            'signature_saved_by' => auth()->id(),
        ]);
        $application->update(['form_data' => $formData]);

        return response()->json(['ok' => true]);
    }

    public function previewLetter(Request $request, Application $application): Response|JsonResponse
    {
        $this->abortUnlessOwnsApplication($application);

        if (! $application->status->canGenerateLetter()) {
            abort(403);
        }

        try {
            $validated = $this->validateLetterPreview($request, $application);

            $html = $this->letters->previewHtml(
                $application,
                $validated['fields'],
                $validated['signature_data'] ?? null,
            );

            if ($this->previewWantsJson($request)) {
                return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
            }

            return response()->view('rt.applications.letter-preview-tab', [
                'application' => $application,
                'fullHtml' => $html,
            ]);
        } catch (\Throwable $e) {
            if ($this->previewWantsJson($request)) {
                return response()->json([
                    'message' => $this->previewErrorMessage($e),
                ], 422);
            }

            throw $e;
        }
    }

    protected function previewWantsJson(Request $request): bool
    {
        return $request->ajax()
            || $request->expectsJson()
            || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }

    protected function previewErrorMessage(\Throwable $e): string
    {
        if ($e instanceof ModelNotFoundException) {
            return 'Template surat belum tersedia. Hubungi admin atau jalankan perintah seed template surat.';
        }

        return $e->getMessage() !== ''
            ? $e->getMessage()
            : 'Gagal membuat pratinjau surat.';
    }

    public function publishLetter(Request $request, Application $application): RedirectResponse
    {
        $this->abortUnlessOwnsApplication($application);

        if (! $application->status->canGenerateLetter()) {
            return back()->withErrors(['letter' => 'Verifikasi berkas terlebih dahulu sebelum menerbitkan surat.']);
        }

        $validated = $this->validateLetterCompose($request, $application, requireSignature: true);

        if (SignatureStorage::isBlank($validated['signature_data'] ?? null)) {
            return back()
                ->withInput()
                ->withErrors(['signature_data' => 'Tanda tangan wajib diisi pada kanvas sebelum menerbitkan surat.']);
        }

        try {
            $this->letters->generate(
                $application,
                $validated['fields'],
                $validated['signature_data'],
                auth()->id(),
            );
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['letter' => 'Gagal menerbitkan surat: '.$e->getMessage()]);
        }

        $formData = $application->form_data ?? [];
        $formData['letter'] = [
            'fields' => $validated['fields'],
            'signed_at' => now()->toIso8601String(),
            'signed_by' => auth()->id(),
        ];
        $application->update(['form_data' => $formData]);
        $application->refresh();
        $application->load('generatedLetter');

        $application->updateQuietly([
            'status' => ApplicationStatus::SiapDiambil,
            'processed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('rt.applications.letter.compose', $application)
            ->with('success', 'Surat PDF berhasil diterbitkan. Anda dapat mengirim PDF ke warga via WhatsApp dari halaman ini.');
    }

    /** @deprecated Redirect ke halaman susun surat */
    public function generateLetter(Application $application): RedirectResponse
    {
        return redirect()->route('rt.applications.letter.compose', $application);
    }

    /**
     * @return array{fields: array<string, string>, signature_data?: string|null}
     */
    protected function validateLetterPreview(Request $request, Application $application): array
    {
        $request->validate([
            'fields' => ['nullable', 'array'],
            'signature_data' => ['nullable', 'string', 'max:500000'],
        ]);

        $defaults = LetterFieldSchema::defaultValues($application);
        $input = $request->input('fields', []);
        if (! is_array($input)) {
            $input = [];
        }

        $merged = array_merge($defaults, $input);
        foreach ($merged as $key => $value) {
            $merged[$key] = is_string($value) ? $value : (string) $value;
        }

        return [
            'fields' => $merged,
            'signature_data' => $request->input('signature_data'),
        ];
    }

    /**
     * @return array{fields: array<string, string>, signature_data?: string}
     */
    protected function validateLetterCompose(Request $request, Application $application, bool $requireSignature): array
    {
        $rules = [
            'fields' => ['required', 'array'],
            ...LetterFieldSchema::validate($application->serviceType->code, $request->input('fields', [])),
            ...LetterFieldSchema::letterNumberValidationRules(),
        ];

        if ($requireSignature) {
            $rules['signature_data'] = ['required', 'string', 'max:500000'];
        } else {
            $rules['signature_data'] = ['nullable', 'string', 'max:500000'];
        }

        return $request->validate($rules);
    }

    public function viewLetter(Application $application): BinaryFileResponse
    {
        $this->abortUnlessOwnsApplication($application);

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

        $headers = [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$safe.'"',
        ];

        if ($mime === 'application/pdf') {
            $headers['Cache-Control'] = 'private, no-cache, must-revalidate';
            $headers['Pragma'] = 'no-cache';
            $headers['Expires'] = '0';
        }

        return $headers;
    }

    public function download(Application $application): StreamedResponse
    {
        $this->abortUnlessOwnsApplication($application);

        $letter = $application->generatedLetter;
        abort_unless($letter && Storage::disk('local')->exists($letter->file_path), 404);

        return Storage::disk('local')->download(
            $letter->file_path,
            'surat-'.$application->application_number.'.pdf'
        );
    }
}
