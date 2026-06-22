<?php

namespace App\Http\Controllers\Rt;

use App\Enums\DomicileStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Rt\Concerns\ResolvesRtProfile;
use App\Http\Controllers\Rt\Concerns\RedirectsAfterPendataanEdit;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Services\GuestResidentService;
use App\Services\PermanentDeletionRequestService;
use App\Services\ResidentFaceReferenceService;
use App\Services\RtPendataanDocumentUpdateService;
use App\Services\RtPendataanRegistrationNotifier;
use App\Support\PhoneNormalizer;
use App\Support\ResidentLetterProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResidentController extends Controller
{
    use ResolvesRtProfile;
    use RedirectsAfterPendataanEdit;

    public function __construct(
        private readonly PermanentDeletionRequestService $deletionRequestService,
        private readonly RtPendataanDocumentUpdateService $pendataanDocumentUpdate,
        private readonly ResidentFaceReferenceService $faceReferences,
        private readonly GuestResidentService $guestResidentService,
        private readonly RtPendataanRegistrationNotifier $pendataanNotifier,
    ) {}

    public function create(Request $request): View
    {
        $rt = $this->requireRtProfile();

        $listQuery = array_filter([
            'filter' => $request->query('filter'),
            'kategori' => $request->query('kategori'),
            'q' => $request->query('q'),
            'household' => $request->query('household'),
        ], fn ($value) => filled($value));

        $preselectedHouseholdId = $request->query('household_id');
        $preselectedHousehold = null;
        if ($preselectedHouseholdId) {
            $preselectedHousehold = Household::forRtProfile($rt)->find($preselectedHouseholdId);
        }

        $backResident = null;
        if ($preselectedHousehold && $request->filled('resident')) {
            $backResident = Resident::query()
                ->whereKey($request->query('resident'))
                ->where('household_id', $preselectedHousehold->id)
                ->first();
        }

        return view('rt.residents.form', [
            'resident' => new Resident,
            'households' => Household::forRtProfile($rt)->orderBy('family_card_number')->get(),
            'preselectedHouseholdId' => $preselectedHousehold?->id ?? $preselectedHouseholdId,
            'preselectedHousehold' => $preselectedHousehold,
            'listQuery' => $listQuery,
            'backResident' => $backResident,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rt = $this->requireRtProfile();
        $validated = $this->validateResident($request, $rt);
        $resident = Resident::create($validated);
        $waLog = $this->pendataanNotifier->notifyAfterRtEntry($resident);

        $listQuery = array_filter([
            'filter' => $request->input('filter', 'aktif'),
            'household' => $resident->household_id,
            'kategori' => $request->input('kategori'),
            'q' => $request->input('q'),
        ], fn ($value) => filled($value));

        return redirect()
            ->route('rt.data-warga.index', $listQuery)
            ->with('success', 'Data warga berhasil ditambahkan.'.$this->pendataanNotifier->flashSuffix($waLog));
    }

    public function show(Request $request, Resident $resident): View
    {
        $this->abortUnlessOwnsResident($resident);

        $resident->load([
            'household.rtProfile',
            'household.headResident',
            'household.pendataanDocuments',
            'household.residents' => function ($q) {
                $q->with('latestNotificationLog')
                    ->orderByDesc('is_head_of_family')
                    ->orderBy('name');
            },
            'latestNotificationLog',
            'verifier',
            'departedByUser',
        ]);

        $listQuery = array_filter([
            'filter' => $request->query('filter'),
            'kategori' => $request->query('kategori'),
            'q' => $request->query('q'),
            'search_by' => $request->query('search_by'),
            'household' => $request->query('household', $resident->household_id),
        ], fn ($value) => $value !== null && $value !== '');

        $faceReadiness = $resident->household
            ? $this->faceReferences->readinessForHousehold($resident->household)
            : null;

        return view('rt.residents.show', [
            'resident' => $resident,
            'listQuery' => $listQuery,
            'faceReadiness' => $faceReadiness,
        ]);
    }

    public function edit(Request $request, Resident $resident): View
    {
        $this->abortUnlessOwnsResident($resident);
        $rt = $this->requireRtProfile();

        $resident->load([
            'household.rtProfile',
            'household.headResident',
            'household.pendataanDocuments',
            'latestNotificationLog',
            'verifier',
            'departedByUser',
        ]);

        $listQuery = array_filter([
            'filter' => $request->query('filter'),
            'kategori' => $request->query('kategori'),
            'q' => $request->query('q'),
            'search_by' => $request->query('search_by'),
            'household' => $request->query('household', $resident->household_id),
        ], fn ($value) => $value !== null && $value !== '');

        $faceReadiness = $resident->household
            ? $this->faceReferences->readinessForHousehold($resident->household)
            : null;

        return view('rt.residents.form', [
            'resident' => $resident,
            'households' => Household::forRtProfile($rt)->orderBy('family_card_number')->get(),
            'listQuery' => $listQuery,
            'faceReadiness' => $faceReadiness,
            'pendataanReturn' => $request->query('return') === 'pendataan' ? $request->query('pendataan_head') : null,
        ]);
    }

    public function update(Request $request, Resident $resident): RedirectResponse
    {
        $this->abortUnlessOwnsResident($resident);
        $rt = $this->requireRtProfile();
        [$residentData, $householdData] = $this->validateResidentUpdate($request, $resident, $rt);

        $faceSyncWarning = null;

        DB::transaction(function () use ($request, $resident, $residentData, $householdData, &$faceSyncWarning) {
            $resident->update($residentData);
            $household = $resident->household;
            $household?->update($householdData);

            if ($household) {
                $faceSyncWarning = $this->pendataanDocumentUpdate->updateFromRequest($household, $request, $resident);
            }
        });

        $request->merge(['household_id' => $resident->household_id]);

        $redirect = $this->redirectAfterPendataanRelatedUpdate($request, 'Data warga berhasil diperbarui.');

        if ($faceSyncWarning) {
            $redirect->with('face_sync_warning', $faceSyncWarning);
        }

        return $redirect;
    }

    public function destroy(Request $request, Resident $resident): RedirectResponse
    {
        $this->abortUnlessOwnsResident($resident);
        $householdId = $resident->household_id;

        $showParams = array_filter([
            'resident' => $resident->id,
            'household' => request('household', $householdId),
            'filter' => request('filter', 'aktif'),
            'kategori' => request('kategori'),
            'q' => request('q'),
        ]);

        $signatureValidator = Validator::make(
            $request->all(),
            ResidentLetterProfile::rtChairSignatureRules(),
            ResidentLetterProfile::rtChairSignatureMessages(),
        );

        if ($signatureValidator->fails()) {
            return redirect()
                ->route('rt.residents.show', $showParams)
                ->withErrors($signatureValidator);
        }

        try {
            $this->deletionRequestService->submitResident($request, $resident, $request->user());
        } catch (ValidationException $e) {
            return redirect()
                ->route('rt.residents.show', $showParams)
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('rt.residents.show', $showParams)
            ->with('success', 'Pengajuan hapus permanen dikirim ke admin kelurahan. Data belum dihapus sampai disetujui.');
    }

    /** @return array<string, mixed> */
    protected function validateResident(Request $request, RtProfile $rt): array
    {
        $isHead = $request->boolean('is_head_of_family');

        $validated = $request->validate([
            'household_id' => ['required', 'exists:households,id'],
            'nik' => ['nullable', 'string', 'size:16'],
            'name' => ['required', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'religion' => [$isHead ? 'required' : 'nullable', 'string', 'max:30'],
            'occupation' => [$isHead ? 'required' : 'nullable', 'string', 'max:100'],
            'education' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'citizenship' => ['nullable', 'string', 'max:30'],
            'phone' => PhoneNormalizer::validationRules(),
            'relationship_to_head' => ['nullable', 'string', 'max:30'],
            'is_head_of_family' => ['boolean'],
            'whatsapp_notify' => ['boolean'],
        ], [
            'religion.required' => 'Agama wajib diisi untuk kepala keluarga (rekap kelurahan).',
            'occupation.required' => 'Pekerjaan wajib diisi untuk kepala keluarga (rekap kelurahan).',
        ]);

        $household = Household::find($validated['household_id']);
        if (! $household || ! RtProfile::householdBelongsToRtNumber((int) $household->rt_profile_id, $rt->rt_number)) {
            throw ValidationException::withMessages([
                'household_id' => 'Kartu keluarga harus berada di wilayah RT Anda.',
            ]);
        }

        $maxMembers = (int) config('kelurahan.pendataan_max_anggota', 50);
        if ($household->residents()->count() >= $maxMembers) {
            throw ValidationException::withMessages([
                'household_id' => "Kartu keluarga sudah mencapai batas maksimal {$maxMembers} anggota.",
            ]);
        }

        if (! empty($validated['nik'])) {
            $existing = $this->guestResidentService->findByNik($validated['nik']);
            if ($existing && ! $existing->domicile_status?->isArchived()) {
                throw ValidationException::withMessages([
                    'nik' => 'NIK sudah terdaftar pada warga aktif.',
                ]);
            }
        }

        if ($request->boolean('is_head_of_family')
            && $household->residents()->where('is_head_of_family', true)->exists()) {
            throw ValidationException::withMessages([
                'is_head_of_family' => 'Kartu keluarga ini sudah memiliki kepala keluarga.',
            ]);
        }

        $validated['is_head_of_family'] = $request->boolean('is_head_of_family');
        $validated['whatsapp_notify'] = true;
        $validated['domicile_status'] = DomicileStatus::Aktif;
        $validated['verified_at'] = now();

        return $validated;
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    protected function validateResidentUpdate(Request $request, Resident $resident, RtProfile $rt): array
    {
        if ($request->has('family_card_number')) {
            $request->merge([
                'family_card_number' => ResidentLetterProfile::normalizeFamilyCardNumber(
                    $request->input('family_card_number')
                ),
            ]);
        }

        $isHead = $request->boolean('is_head_of_family');

        $validated = $request->validate(array_merge([
            'nik' => ['nullable', 'string', 'size:16'],
            'name' => ['required', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'religion' => [$isHead ? 'required' : 'nullable', 'string', 'max:30'],
            'occupation' => [$isHead ? 'required' : 'nullable', 'string', 'max:100'],
            'education' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'citizenship' => ['nullable', 'string', 'max:30'],
            'phone' => PhoneNormalizer::validationRules(),
            'relationship_to_head' => ['nullable', 'string', 'max:30'],
            'is_head_of_family' => ['boolean'],
            'whatsapp_notify' => ['boolean'],
        ], ResidentLetterProfile::householdFormValidationRules()), array_merge([
            'religion.required' => 'Agama wajib diisi untuk kepala keluarga (rekap kelurahan).',
            'occupation.required' => 'Pekerjaan wajib diisi untuk kepala keluarga (rekap kelurahan).',
        ], ResidentLetterProfile::householdFormValidationMessages()));

        $household = $resident->household;
        if (! $household || ! RtProfile::householdBelongsToRtNumber((int) $household->rt_profile_id, $rt->rt_number)) {
            throw ValidationException::withMessages([
                'household' => 'Kartu keluarga harus berada di wilayah RT Anda.',
            ]);
        }

        $residentData = [
            'household_id' => $resident->household_id,
            'nik' => $validated['nik'] ?? null,
            'name' => $validated['name'],
            'birth_place' => $validated['birth_place'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'education' => $validated['education'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'citizenship' => $validated['citizenship'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'relationship_to_head' => $validated['relationship_to_head'] ?? null,
            'is_head_of_family' => $request->boolean('is_head_of_family'),
            'whatsapp_notify' => true,
        ];

        return [$residentData, ResidentLetterProfile::householdFieldsFromValidated($validated)];
    }
}
