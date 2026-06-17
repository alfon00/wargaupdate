<?php

namespace App\Http\Controllers\Public;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ServiceType;
use App\Services\GuestResidentService;
use App\Support\LetterSubjectSchema;
use App\Support\ResidentLetterProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class GuestApplicationController extends Controller
{
    public function __construct(
        protected GuestResidentService $guestResidents,
    ) {}

    public function create(ServiceType $service): View|RedirectResponse
    {
        abort_unless($service->is_active, 404);

        $request = request();
        $resident = $this->guestResidents->residentFromSuratSession($request);
        if (! $resident) {
            $request->session()->put('surat_intended_service_code', $service->code);

            return redirect()->route('services.surat.verify-form');
        }

        return view('public.services.apply', [
            'service' => $service,
            'rtProfiles' => GuestResidentService::rtProfilesForSelect(),
            'resident' => $resident,
            'profileIncomplete' => ! ResidentLetterProfile::isComplete($resident),
            'missingProfileLabels' => ResidentLetterProfile::missingLabels($resident),
        ]);
    }

    public function store(Request $request, ServiceType $service): RedirectResponse
    {
        abort_unless($service->is_active, 404);

        $sessionResidentId = $request->session()->get('surat_resident_id');
        if (! $sessionResidentId) {
            $request->session()->put('surat_intended_service_code', $service->code);

            return redirect()
                ->route('services.surat.verify-form')
                ->with('info', 'Verifikasi identitas terlebih dahulu untuk melanjutkan permohonan.');
        }

        $rtProfileId = $this->guestResidents->ensureRtInauga((int) $request->input('rt_profile_id'));
        $request->merge(['rt_profile_id' => $rtProfileId]);

        $validated = $request->validate([
            ...$this->guestResidents->applyPemohonRules(),
            'purpose' => ['required', 'string', 'max:500'],
            ...$this->businessFieldRules($service),
            ...$this->documentRules($service),
        ], $this->documentValidationMessages($service));

        $resident = $this->guestResidents->resolveVerifiedResidentForApplication($request, (int) $sessionResidentId);
        $this->guestResidents->assertLetterProfileComplete($resident->fresh(['household']));

        $rt = \App\Models\RtProfile::findOrFail($rtProfileId);

        $formData = [
            'channel' => 'layanan_publik_tanpa_login',
            'subject_count' => 1,
            'letter_subjects' => LetterSubjectSchema::forPemohon($resident),
        ];

        if ($service->code === 'surat_usaha') {
            $formData['letter'] = [
                'fields' => [
                    'nama_usaha' => $validated['nama_usaha'],
                    'jenis_usaha' => $validated['jenis_usaha'],
                    'alamat_usaha' => $validated['alamat_usaha'],
                ],
            ];
        }

        $application = Application::create([
            'application_number' => Application::generateNumber($rt->rt_number),
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $rtProfileId,
            'submitted_by' => null,
            'status' => ApplicationStatus::Diajukan,
            'purpose' => $validated['purpose'],
            'submitted_at' => now(),
            'form_data' => $formData,
        ]);

        foreach ($request->file('documents', []) as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('application-documents/'.$application->id, 'local');
            ApplicationDocument::create([
                'application_id' => $application->id,
                'document_type' => 'req_'.$index,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
            ]);
        }

        $this->guestResidents->endSuratSession($request);

        return redirect()
            ->route('services.apply.success', $application)
            ->with('success', 'Permohonan berhasil dikirim.');
    }

    public function success(Request $request, Application $application): View
    {
        $this->guestResidents->endSuratSession($request);

        $application->load(['serviceType', 'resident']);

        return view('public.services.success', compact('application'));
    }

    /** @return array<string, mixed> */
    private function businessFieldRules(ServiceType $service): array
    {
        if ($service->code !== 'surat_usaha') {
            return [];
        }

        return [
            'nama_usaha' => ['required', 'string', 'max:255'],
            'jenis_usaha' => ['required', 'string', 'max:255'],
            'alamat_usaha' => ['required', 'string', 'max:500'],
        ];
    }

    /** @return array<string, mixed> */
    private function documentRules(ServiceType $service): array
    {
        $requiredCount = count($service->required_fields ?? []);
        if ($requiredCount === 0) {
            return [];
        }

        return [
            'documents' => ['required', 'array', 'size:'.$requiredCount],
            'documents.*' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    /** @return array<string, string> */
    private function documentValidationMessages(ServiceType $service): array
    {
        $messages = [
            'documents.required' => 'Unggah semua berkas persyaratan.',
            'documents.size' => 'Jumlah berkas tidak sesuai persyaratan layanan.',
            'documents.*.required' => 'Setiap berkas persyaratan wajib diunggah.',
            'documents.*.uploaded' => 'Berkas gagal diunggah. Pastikan maks. 5 MB dan format PDF/JPG/PNG.',
            'documents.*.max' => 'Berkas tidak boleh lebih dari 5 MB.',
            'documents.*.mimes' => 'Berkas harus berformat PDF, JPG, atau PNG.',
        ];

        foreach ($service->required_fields ?? [] as $index => $field) {
            $label = match ($field) {
                'KK' => 'Kartu Keluarga (KK)',
                'KTP' => 'KTP atau KIA',
                default => $field,
            };
            $messages['documents.'.$index.'.required'] = $label.' wajib diunggah.';
        }

        return $messages;
    }
}
