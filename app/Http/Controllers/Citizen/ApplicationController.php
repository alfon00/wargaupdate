<?php

namespace App\Http\Controllers\Citizen;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function index(): View
    {
        $resident = auth()->user()->resident;
        abort_unless($resident, 403, 'Akun belum terhubung ke data warga.');

        $applications = Application::with('serviceType')
            ->where('resident_id', $resident->id)
            ->latest()
            ->paginate(10);

        return view('citizen.applications.index', compact('applications'));
    }

    public function create(Request $request): View
    {
        $resident = auth()->user()->resident;
        abort_unless($resident, 403, 'Akun belum terhubung ke data warga.');

        return view('citizen.applications.create', [
            'services' => ServiceType::where('is_active', true)->orderBy('name')->get(),
            'selectedService' => $request->query('layanan')
                ? ServiceType::where('code', $request->query('layanan'))->first()
                : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $resident = auth()->user()->resident;
        abort_unless($resident, 403, 'Akun belum terhubung ke data warga.');

        $service = ServiceType::findOrFail($request->input('service_type_id'));

        $validated = $request->validate([
            'service_type_id' => ['required', 'exists:service_types,id'],
            'purpose' => ['required', 'string', 'max:500'],
            'documents.*' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            ...$this->businessFieldRules($service),
        ]);

        $rtNumber = $resident->household?->rtProfile?->rt_number;

        $formData = ['channel' => 'portal_warga'];
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
            'application_number' => Application::generateNumber($rtNumber),
            'service_type_id' => $validated['service_type_id'],
            'resident_id' => $resident->id,
            'submitted_by' => auth()->id(),
            'status' => ApplicationStatus::Diajukan,
            'purpose' => $validated['purpose'],
            'submitted_at' => now(),
            'form_data' => $formData,
        ]);

        if ($request->hasFile('documents')) {
            $service = $application->serviceType;
            $requirements = $service->required_fields ?? [];

            foreach ($request->file('documents') as $index => $file) {
                $path = $file->store('application-documents/'.$application->id, 'local');
                ApplicationDocument::create([
                    'application_id' => $application->id,
                    'document_type' => isset($requirements[$index]) ? 'req_'.$index : 'lampiran_'.($index + 1),
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()
            ->route('citizen.applications.show', $application)
            ->with('success', 'Permohonan berhasil diajukan. Nomor: '.$application->application_number);
    }

    public function show(Application $application): View
    {
        $this->authorizeApplication($application);

        $application->load(['serviceType', 'generatedLetter', 'documents']);

        return view('citizen.applications.show', compact('application'));
    }

    public function download(Application $application): StreamedResponse
    {
        $this->authorizeApplication($application);

        $letter = $application->generatedLetter;
        abort_unless($letter && Storage::disk('local')->exists($letter->file_path), 404);

        return Storage::disk('local')->download(
            $letter->file_path,
            'surat-'.$application->application_number.'.pdf'
        );
    }

    protected function authorizeApplication(Application $application): void
    {
        $resident = auth()->user()->resident;
        abort_unless($resident && $application->resident_id === $resident->id, 403);
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
}
