<?php

namespace App\Services;

use App\Enums\DomicileStatus;
use App\Jobs\SendPendataanWhatsApp;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Support\PhoneNormalizer;
use App\Support\ResidentLetterProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GuestResidentService
{
    public function __construct(
        private readonly PendataanDocumentStorage $pendataanDocumentStorage,
        private readonly FaceVerificationService $faceVerification,
    ) {}

    /** @return array<string, mixed> */
    public function applyPemohonRules(): array
    {
        return [
            'nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => PhoneNormalizer::validationRules(true),
            'rt_profile_id' => ['required', 'exists:rt_profiles,id'],
            'whatsapp_notify' => ['boolean'],
        ];
    }

    /** @return array<string, mixed> */
    public function identityRules(bool $requireUniqueNik = false): array
    {
        $nikRule = ['required', 'string', 'size:16'];
        if ($requireUniqueNik) {
            $nikRule[] = 'unique:residents,nik';
        }

        return [
            'nik' => $nikRule,
            'name' => ['required', 'string', 'max:255'],
            'phone' => PhoneNormalizer::validationRules(true),
            'rt_profile_id' => ['required', 'exists:rt_profiles,id'],
            'house_number' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'gender' => ['required', 'string', Rule::in(['Laki-laki', 'Perempuan'])],
            'whatsapp_notify' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function pendataanDocumentMessages(): array
    {
        return [
            'document_kk.required' => 'Kartu Keluarga (KK) wajib diunggah.',
            'document_kk.uploaded' => 'Kartu Keluarga (KK) gagal diunggah. Pastikan ukuran maks. 5 MB, format PDF/JPG/PNG, dan coba lagi.',
            'document_kk.max' => 'Kartu Keluarga (KK) tidak boleh lebih dari 5 MB.',
            'document_kk.mimes' => 'Kartu Keluarga (KK) harus berformat PDF, JPG, atau PNG.',
            'document_ktp.required' => 'KTP Kepala KK wajib diunggah.',
            'document_ktp.uploaded' => 'KTP Kepala KK gagal diunggah. Pastikan ukuran maks. 5 MB, format PDF/JPG/PNG, dan coba lagi.',
            'document_ktp.max' => 'KTP Kepala KK tidak boleh lebih dari 5 MB.',
            'document_ktp.mimes' => 'KTP Kepala KK harus berformat PDF, JPG, atau PNG.',
            'documents.*.uploaded' => 'Lampiran tambahan gagal diunggah. Pastikan ukuran maks. 5 MB per berkas.',
            'documents.*.max' => 'Lampiran tambahan tidak boleh lebih dari 5 MB per berkas.',
            'documents.*.mimes' => 'Lampiran tambahan harus berformat PDF, JPG, atau PNG.',
        ];
    }

    /** @return array<string, mixed> */
    protected function memberDemographicRules(): array
    {
        $rules = [];
        foreach (ResidentLetterProfile::demographicValidationRules('members.*') as $key => $rule) {
            $rules[$key] = $rule;
        }

        return $rules;
    }

    public function findByNik(string $nik): ?Resident
    {
        $normalized = preg_replace('/\D/', '', $nik);
        if ($normalized === '') {
            return null;
        }

        return Resident::where('nik', $normalized)->first();
    }

    public function register(Request $request): Resident
    {
        $validated = $request->validate($this->identityRules(requireUniqueNik: true));
        $validated['rt_profile_id'] = $this->resolveCanonicalRtProfileId((int) $validated['rt_profile_id']);

        return $this->createResident($validated);
    }

    public function storePendataanDocuments(Household $household, Request $request): ?string
    {
        $this->storePendataanFile($household, $request->file('document_kk'), 'kk');
        $this->storePendataanFile($household, $request->file('document_ktp'), 'ktp_kepala');

        foreach ($request->file('documents', []) ?? [] as $file) {
            if ($file instanceof UploadedFile) {
                $this->storePendataanFile($household, $file, 'lampiran');
            }
        }

        return $this->pendataanDocumentStorage->consumeFaceSyncWarning();
    }

    protected function storePendataanFile(Household $household, ?UploadedFile $file, string $documentType): void
    {
        if (! $file) {
            return;
        }

        $this->pendataanDocumentStorage->store($household, $file, $documentType);
    }

    /** @param  array<string, mixed>  $member */
    protected function normalizeMemberRow(array $member, bool $isHead): array
    {
        $noNik = filter_var($member['no_nik'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $nik = $noNik ? null : ($member['nik'] ?? null);
        if ($nik !== null) {
            $nik = preg_replace('/\D/', '', $nik);
            if (strlen($nik) !== 16) {
                throw ValidationException::withMessages([
                    'members' => 'NIK harus 16 digit jika diisi.',
                ]);
            }
        }

        return [
            'nik' => $nik,
            'name' => $member['name'],
            'birth_place' => $member['birth_place'] ?? null,
            'birth_date' => $member['birth_date'] ?? null,
            'gender' => $member['gender'] ?? null,
            'relationship' => $member['relationship'] ?? ($isHead ? 'Kepala Keluarga' : 'Anggota Keluarga'),
            'occupation' => $member['occupation'] ?? null,
            'education' => $member['education'] ?? null,
            'religion' => $member['religion'] ?? null,
            'marital_status' => $member['marital_status'] ?? null,
            'citizenship' => $member['citizenship'] ?? 'WNI',
        ];
    }

    public function assertLetterProfileComplete(Resident $resident): void
    {
        ResidentLetterProfile::assertComplete($resident);
    }

    public function resolveForApplication(Request $request): Resident
    {
        $validated = $request->validate($this->identityRules());
        $validated['rt_profile_id'] = $this->resolveCanonicalRtProfileId((int) $validated['rt_profile_id']);

        $existing = $this->findByNik($validated['nik']);
        if ($existing) {
            $this->assertResidentBelongsToRtProfile($existing, (int) $validated['rt_profile_id']);
            $this->syncContactInfo($existing, $validated);

            return $existing;
        }

        return $this->createResident($validated);
    }

    /**
     * @param  array{rt_profile_id: int|string, nik: string, phone: string}  $data
     */
    public function verifyForLetterService(array $data): Resident
    {
        $rtProfileId = $this->ensureRtInauga((int) $data['rt_profile_id']);
        $nik = preg_replace('/\D/', '', $data['nik']) ?? '';

        $resident = $this->findByNik($nik);
        if (! $resident) {
            throw ValidationException::withMessages([
                'nik' => 'NIK tidak ditemukan. Hubungi pengurus RT atau lakukan pendataan ulang jika sudah terdaftar.',
            ]);
        }

        $phoneVariants = PhoneNormalizer::variants($data['phone']);
        $registeredPhone = $resident->registeredPhoneForVerification();
        $residentPhone = PhoneNormalizer::digits($registeredPhone);
        if (! in_array($residentPhone, array_map([PhoneNormalizer::class, 'digits'], $phoneVariants), true)
            && ! in_array($registeredPhone, PhoneNormalizer::variants($data['phone']), true)) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor HP tidak cocok dengan data terdaftar.',
            ]);
        }

        $this->assertResidentBelongsToRtProfile($resident, $rtProfileId);
        $this->assertResidentEligibleForLetter($resident);

        return $resident;
    }

    public function assertResidentEligibleForLetter(Resident $resident): void
    {
        if ($resident->domicile_status === DomicileStatus::MenungguVerifikasi) {
            throw ValidationException::withMessages([
                'nik' => 'Data Anda masih menunggu verifikasi pengurus RT. Setelah disetujui, Anda dapat mengajukan surat pengantar.',
            ]);
        }

        if ($resident->domicile_status?->isArchived()) {
            $label = $resident->domicile_status->label();
            throw ValidationException::withMessages([
                'nik' => "Data Anda dicatat sebagai {$label} di RT ini dan tidak dapat mengajukan surat pengantar. Hubungi pengurus RT jika perlu koreksi.",
            ]);
        }

        if ($resident->domicile_status !== DomicileStatus::Aktif) {
            throw ValidationException::withMessages([
                'nik' => 'Status data warga belum aktif. Hubungi pengurus RT untuk bantuan.',
            ]);
        }
    }

    public function resolveVerifiedResidentForApplication(Request $request, int $sessionResidentId): Resident
    {
        $resident = Resident::with('household')->findOrFail($sessionResidentId);

        $validated = $request->validate($this->applyPemohonRules());
        $validated['rt_profile_id'] = $this->resolveCanonicalRtProfileId((int) $validated['rt_profile_id']);
        $validated['whatsapp_notify'] = true;

        $requestNik = preg_replace('/\D/', '', $validated['nik']);
        if ($requestNik !== $resident->nik) {
            throw ValidationException::withMessages([
                'nik' => 'NIK tidak sesuai dengan identitas yang diverifikasi. Muat ulang halaman dan coba lagi.',
            ]);
        }

        $this->assertResidentEligibleForLetter($resident);
        $this->assertResidentBelongsToRtProfile($resident, (int) $validated['rt_profile_id']);
        $this->syncContactInfo($resident, $validated);

        return $resident->fresh(['household']);
    }

    public function residentFromSuratSession(Request $request): ?Resident
    {
        $id = $request->session()->get('surat_resident_id');
        if (! $id) {
            return null;
        }

        return Resident::with('household.rtProfile')->find($id);
    }

    public function endSuratSession(Request $request): void
    {
        $request->session()->forget(['surat_resident_id', 'surat_intended_service_code']);
    }

    public function assertResidentBelongsToRtProfile(Resident $resident, int $canonicalRtProfileId): void
    {
        $rt = RtProfile::findOrFail($canonicalRtProfileId);
        $profileIds = RtProfile::profileIdsForRtNumber($rt->rt_number);
        $householdRtId = (int) $resident->household?->rt_profile_id;

        if (! $householdRtId || ! in_array($householdRtId, $profileIds, true)) {
            throw ValidationException::withMessages([
                'rt_profile_id' => 'NIK terdaftar di RT lain. Pilih RT yang sesuai.',
            ]);
        }
    }

    /** @param  array<string, mixed>  $data */
    protected function createResident(array $data): Resident
    {
        $data['rt_profile_id'] = $this->resolveCanonicalRtProfileId((int) $data['rt_profile_id']);
        $rt = RtProfile::findOrFail($data['rt_profile_id']);

        $household = Household::create([
            'rt_profile_id' => $rt->id,
            'house_number' => $data['house_number'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => 'aktif',
        ]);

        return Resident::create([
            'household_id' => $household->id,
            'nik' => $data['nik'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'birth_place' => $data['birth_place'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'gender' => $data['gender'] ?? null,
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'whatsapp_notify' => $data['whatsapp_notify'] ?? true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);
    }

    /** @param  array<string, mixed>  $data */
    protected function syncContactInfo(Resident $resident, array $data): void
    {
        $resident->update([
            'phone' => $data['phone'],
            'whatsapp_notify' => true,
        ]);
    }

    public function ensureRtInauga(int $rtProfileId): int
    {
        return $this->resolveCanonicalRtProfileId($rtProfileId);
    }

    public function resolveCanonicalRtProfileId(int $rtProfileId): int
    {
        $profile = RtProfile::inauga()->find($rtProfileId);
        if (! $profile) {
            throw ValidationException::withMessages([
                'rt_profile_id' => 'Pilih RT di wilayah Kelurahan Inauga.',
            ]);
        }

        $canonicalId = RtProfile::canonicalProfileIdForRtNumber($profile->rt_number);
        if (! $canonicalId) {
            throw ValidationException::withMessages([
                'rt_profile_id' => 'Profil RT tidak valid.',
            ]);
        }

        return $canonicalId;
    }

    public static function rtProfilesForSelect()
    {
        return RtProfile::forPublicSelect()->withRegisteredStaff()->get();
    }

    /** @return list<array<string, mixed>> */
    public function membersForPendataanUlangForm(Household $household): array
    {
        $residents = $household->residents()
            ->orderByDesc('is_head_of_family')
            ->orderBy('id')
            ->get();

        return $residents->map(fn (Resident $r) => [
            'resident_id' => $r->id,
            'name' => $r->name,
            'nik' => $r->nik,
            'no_nik' => ! filled($r->nik),
            'relationship' => $r->relationship_to_head ?? ($r->is_head_of_family ? 'Kepala Keluarga' : 'Anggota Keluarga'),
            'birth_place' => $r->birth_place,
            'birth_date' => $r->birth_date?->format('Y-m-d'),
            'gender' => $r->gender,
            'occupation' => $r->occupation,
            'education' => $r->education,
            'religion' => $r->religion,
            'marital_status' => $r->marital_status,
            'citizenship' => $r->citizenship ?? 'WNI',
        ])->values()->all();
    }

    /** @return array<string, mixed> */
    public function pendataanUlangDocumentRules(Household $household): array
    {
        $max = (int) config('kelurahan.pendataan_max_anggota', 50);
        $residentCount = max(1, $household->residents()->count());

        return [
            'document_kk' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'whatsapp_notify' => ['boolean'],
            'members' => ['required', 'array', 'size:'.$residentCount, 'max:'.$max],
            'members.*.resident_id' => [
                'required',
                'integer',
                Rule::exists('residents', 'id')->where('household_id', $household->id),
            ],
            'members.*.document_id' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    /**
     * @return array{household: Household, head: Resident}
     */
    public function submitPendataanUlangDocuments(Request $request, Household $household): array
    {
        $orderedResidents = $this->orderedHouseholdResidents($household);
        $residentCount = $orderedResidents->count();
        abort_unless($residentCount > 0, 404);

        $validated = $request->validate(
            $this->pendataanUlangDocumentRules($household),
            [
                ...$this->pendataanDocumentMessages(),
                'members.required' => 'Unggah KTP atau KIA untuk setiap anggota keluarga.',
                'members.size' => 'Unggah KTP atau KIA untuk setiap anggota keluarga ('.$residentCount.' orang).',
                'members.*.document_id.required' => 'Unggah KTP atau KIA untuk setiap anggota keluarga.',
                'members.*.document_id.uploaded' => 'Berkas identitas anggota gagal diunggah. Pastikan maks. 5 MB dan format PDF/JPG/PNG.',
            ],
        );

        $submittedIds = collect($validated['members'])->pluck('resident_id')->map(fn ($id) => (int) $id);
        if ($submittedIds->unique()->count() !== $residentCount) {
            throw ValidationException::withMessages([
                'members' => 'Unggah KTP atau KIA untuk setiap anggota keluarga (tanpa duplikat).',
            ]);
        }

        $indexByResidentId = $orderedResidents->values()->mapWithKeys(
            fn (Resident $resident, int $index) => [$resident->id => $index]
        );

        $result = DB::transaction(function () use ($request, $validated, $household, $orderedResidents, $indexByResidentId) {
            $household->update([
                'status' => 'menunggu_verifikasi',
                'pendataan_category' => 'pendataan_ulang',
            ]);

            $notifyEnabled = $request->boolean('whatsapp_notify', true);

            $head = null;
            foreach ($orderedResidents as $resident) {
                $updates = [
                    'domicile_status' => DomicileStatus::MenungguVerifikasi,
                    'whatsapp_notify' => $notifyEnabled,
                ];
                if ($resident->is_head_of_family) {
                    $head = $resident;
                }
                $resident->update($updates);
            }

            foreach ($validated['members'] as $i => $memberRow) {
                $residentId = (int) $memberRow['resident_id'];
                $member = $orderedResidents->firstWhere('id', $residentId);
                if (! $member) {
                    continue;
                }

                $index = $indexByResidentId[$residentId] ?? $i;
                $file = $request->file("members.{$i}.document_id");
                if ($file instanceof UploadedFile) {
                    $docType = $this->memberIdentityDocumentType(
                        $member->birth_date?->format('Y-m-d'),
                        $index
                    );
                    $this->storePendataanFile($household, $file, $docType);
                }
            }

            $this->storePendataanFile($household, $request->file('document_kk'), 'kk');

            $head ??= $orderedResidents->firstWhere('is_head_of_family', true)
                ?? $orderedResidents->first();

            return [
                'household' => $household->fresh(['rtProfile', 'pendataanDocuments']),
                'head' => $head,
            ];
        });

        if ($result['head']) {
            SendPendataanWhatsApp::dispatch($result['head']->id, 'pendataan_submitted');
        }

        return $result;
    }

    /** @return \Illuminate\Support\Collection<int, Resident> */
    public function orderedHouseholdResidents(Household $household)
    {
        return $household->residents()
            ->orderByDesc('is_head_of_family')
            ->orderBy('id')
            ->get();
    }

    public function memberIdentityDocumentTypeForResident(Resident $member, int $index): string
    {
        return $this->memberIdentityDocumentType($member->birth_date?->format('Y-m-d'), $index);
    }

    public function residentIndexInHousehold(Resident $resident): ?int
    {
        $household = $resident->household;
        if (! $household) {
            return null;
        }

        $index = $this->orderedHouseholdResidents($household)
            ->search(fn (Resident $member) => (int) $member->id === (int) $resident->id);

        return $index === false ? null : $index;
    }

    /** @return list<string> */
    public function identityDocumentTypesForResident(Resident $resident): array
    {
        $household = $resident->household;
        if (! $household) {
            return [];
        }

        $index = $this->residentIndexInHousehold($resident);
        if ($index === null) {
            return [];
        }

        $types = [$this->memberIdentityDocumentTypeForResident($resident, $index)];

        if ($index === 0 && $household->pendataanDocuments->contains('document_type', 'ktp_kepala')) {
            $types[] = 'ktp_kepala';
        }

        return array_values(array_unique($types));
    }

    public function primaryIdentityDocumentTypeForResident(Resident $resident): string
    {
        $household = $resident->household;
        $index = $this->residentIndexInHousehold($resident);

        if (! $household || $index === null) {
            return $this->memberIdentityDocumentTypeForResident($resident, 0);
        }

        if ($index === 0 && $household->pendataanDocuments->contains('document_type', 'ktp_kepala')) {
            return 'ktp_kepala';
        }

        return $this->memberIdentityDocumentTypeForResident($resident, $index);
    }

    public function memberUsesKiaDocument(Resident $resident): bool
    {
        return str_starts_with(
            $this->primaryIdentityDocumentTypeForResident($resident),
            'kia_'
        );
    }

    /** @return array<string, mixed> */
    public function pendataanUlangRules(Household $household): array
    {
        $max = (int) config('kelurahan.pendataan_max_anggota', 50);

        return [
            'family_card_number' => [
                'required',
                'string',
                'size:16',
                'regex:/^\d{16}$/',
                Rule::unique('households', 'family_card_number')->ignore($household->id),
            ],
            'house_number' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            ...ResidentLetterProfile::householdRecapValidationRules(),
            'phone' => PhoneNormalizer::validationRules(true),
            'whatsapp_notify' => ['boolean'],
            'document_kk' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'members' => ['required', 'array', 'min:1', 'max:'.$max],
            'members.*.resident_id' => ['nullable', 'integer', 'exists:residents,id'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.no_nik' => ['sometimes', 'boolean'],
            'members.*.nik' => ['nullable', 'string', 'size:16'],
            'members.*.birth_place' => ['required', 'string', 'max:100'],
            'members.*.birth_date' => ['required', 'date'],
            'members.*.gender' => ['required', 'string', Rule::in(['Laki-laki', 'Perempuan'])],
            'members.*.relationship' => ['nullable', 'string', 'max:30'],
            'members.*.document_id' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            ...$this->memberDemographicRules(),
        ];
    }

    /**
     * @return array{household: Household, head: Resident}
     */
    public function submitPendataanUlang(Request $request, Household $household): array
    {
        if ($request->has('family_card_number')) {
            $request->merge([
                'family_card_number' => ResidentLetterProfile::normalizeFamilyCardNumber(
                    $request->input('family_card_number')
                ),
            ]);
        }

        $validated = $request->validate(
            $this->pendataanUlangRules($household),
            [
                ...ResidentLetterProfile::familyCardNumberMessages(),
                ...ResidentLetterProfile::householdRecapMessages(),
                ...$this->pendataanDocumentMessages(),
                'members.*.document_id.required' => 'Unggah KTP atau KIA untuk setiap anggota keluarga.',
                'members.*.document_id.uploaded' => 'Berkas identitas anggota gagal diunggah. Pastikan maks. 5 MB dan format PDF/JPG/PNG.',
            ],
        );

        $members = $this->normalizePendataanUlangMembers($validated);

        return DB::transaction(function () use ($request, $validated, $members, $household) {
            $household->update([
                'family_card_number' => $validated['family_card_number'],
                'house_number' => $validated['house_number'] ?? $household->house_number,
                'address' => $validated['address'],
                'status' => 'menunggu_verifikasi',
                'pendataan_category' => 'pendataan_ulang',
                'status_rumah_tinggal' => $validated['status_rumah_tinggal'],
                'suku' => $validated['suku'],
                'kondisi_rumah_milik' => $validated['kondisi_rumah_milik'] ?? null,
            ]);

            $head = null;
            $notifyEnabled = $validated['whatsapp_notify'] ?? true;
            foreach ($members as $i => $member) {
                $isHead = $i === 0;
                $resident = null;

                if (! empty($member['resident_id'])) {
                    $resident = $household->residents()->find($member['resident_id']);
                }

                $payload = [
                    'name' => $member['name'],
                    'nik' => $member['nik'],
                    'birth_place' => $member['birth_place'],
                    'birth_date' => $member['birth_date'],
                    'gender' => $member['gender'],
                    'is_head_of_family' => $isHead,
                    'relationship_to_head' => $member['relationship'],
                    'phone' => $isHead ? $validated['phone'] : null,
                    'whatsapp_notify' => $notifyEnabled,
                    'domicile_status' => DomicileStatus::MenungguVerifikasi,
                    ...ResidentLetterProfile::demographicAttributesFromInput($member),
                ];

                if ($resident) {
                    $resident->update($payload);
                } else {
                    $resident = Resident::create([
                        'household_id' => $household->id,
                        ...$payload,
                    ]);
                }

                if ($isHead) {
                    $head = $resident;
                }

                $file = $request->file("members.{$i}.document_id");
                if ($file instanceof UploadedFile) {
                    $docType = $this->memberIdentityDocumentType($member['birth_date'], $i);
                    $this->storePendataanFile($household, $file, $docType);
                }
            }

            $this->storePendataanFile($household, $request->file('document_kk'), 'kk');

            $head ??= $household->residents()->where('is_head_of_family', true)->first()
                ?? $household->residents()->first();

            return [
                'household' => $household->fresh(['rtProfile', 'pendataanDocuments']),
                'head' => $head,
            ];
        });
    }

    /** @param  array<string, mixed>  $validated */
    protected function normalizePendataanUlangMembers(array $validated): array
    {
        $raw = array_values($validated['members'] ?? []);
        $normalized = [];

        foreach ($raw as $i => $member) {
            $rel = $member['relationship'] ?? ($i === 0 ? 'Kepala Keluarga' : 'Anggota Keluarga');
            $row = $this->normalizeMemberRow(
                array_merge($member, ['relationship' => $rel]),
                $i === 0
            );
            $row['resident_id'] = $member['resident_id'] ?? null;
            $normalized[] = $row;
        }

        return $normalized;
    }

    /**
     * @return array{household: Household, head: Resident}
     */
    public function submitPendataanWargaBaru(Request $request): array
    {
        $this->mergePendataanFaceDescriptorInputs($request);

        if ($request->has('family_card_number')) {
            $request->merge([
                'family_card_number' => ResidentLetterProfile::normalizeFamilyCardNumber(
                    $request->input('family_card_number')
                ),
            ]);
        }

        $rtProfileId = $this->ensureRtInauga((int) $request->input('rt_profile_id'));
        $request->merge(['rt_profile_id' => $rtProfileId]);
        $rt = RtProfile::findOrFail($rtProfileId);

        $maxMembers = (int) config('kelurahan.pendataan_max_anggota', 50);

        $validated = $request->validate(
            $this->pendataanWargaBaruRules($maxMembers),
            [
                ...ResidentLetterProfile::familyCardNumberMessages(),
                ...ResidentLetterProfile::householdRecapMessages(),
                ...$this->pendataanDocumentMessages(),
                'members.*.document_id.required' => 'Unggah KTP atau KIA untuk setiap anggota keluarga.',
                'members.*.document_id.uploaded' => 'Berkas identitas anggota gagal diunggah. Pastikan maks. 5 MB dan format PDF/JPG/PNG.',
                'members.*.nik.distinct' => 'NIK tiap anggota keluarga harus berbeda. Periksa kembali NIK yang sama.',
            ],
        );

        $validated['whatsapp_notify'] = $request->boolean('whatsapp_notify', true);

        $members = $this->normalizePendataanWargaMembers($validated['members'] ?? []);

        foreach ($members as $index => $member) {
            $existing = $this->findByNik($member['nik']);
            if ($existing && ! $existing->domicile_status?->isArchived()) {
                throw ValidationException::withMessages([
                    "members.{$index}.nik" => 'NIK sudah terdaftar pada warga aktif.',
                ]);
            }
        }

        $result = DB::transaction(function () use ($request, $validated, $members, $rt) {
            $registrationType = count($members) > 1 ? 'keluarga' : 'perorangan';

            $household = Household::create([
                'rt_profile_id' => $rt->id,
                'family_card_number' => $validated['family_card_number'],
                'house_number' => $validated['house_number'] ?? null,
                'address' => $validated['address'],
                'status' => 'menunggu_verifikasi',
                'registration_type' => $registrationType,
                'pendataan_category' => 'warga_baru',
                'status_rumah_tinggal' => $validated['status_rumah_tinggal'],
                'suku' => $validated['suku'],
                'kondisi_rumah_milik' => $validated['kondisi_rumah_milik'] ?? null,
            ]);

            $head = null;
            $notifyEnabled = $validated['whatsapp_notify'];
            foreach ($members as $i => $member) {
                $isHead = $i === 0;
                $resident = Resident::create([
                    'household_id' => $household->id,
                    'nik' => $member['nik'],
                    'name' => $member['name'],
                    'phone' => $isHead ? $validated['phone'] : null,
                    'birth_place' => $member['birth_place'],
                    'birth_date' => $member['birth_date'],
                    'gender' => $member['gender'],
                    'is_head_of_family' => $isHead,
                    'relationship_to_head' => $member['relationship'],
                    'whatsapp_notify' => $notifyEnabled,
                    'domicile_status' => DomicileStatus::MenungguVerifikasi,
                    ...ResidentLetterProfile::demographicAttributesFromInput($member),
                ]);

                if ($isHead) {
                    $head = $resident;
                }

                $file = $request->file("members.{$i}.document_id");
                if ($file instanceof UploadedFile) {
                    $docType = $this->memberIdentityDocumentType($member['birth_date'], $i);
                    $this->storePendataanFile($household, $file, $docType);
                }
            }

            $this->storePendataanFile($household, $request->file('document_kk'), 'kk');
            $this->storeHeadSelfieDocument($household, $validated['head_selfie_data']);

            $head ??= $household->residents()->where('is_head_of_family', true)->first()
                ?? $household->residents()->first();

            return [
                'household' => $household->fresh(['rtProfile']),
                'head' => $head,
            ];
        });

        if ($result['head']) {
            SendPendataanWhatsApp::dispatch($result['head']->id, 'pendataan_submitted');
        }

        return $result;
    }

    /** @return array<string, mixed> */
    public function pendataanWargaBaruRules(int $maxMembers): array
    {
        return [
            'rt_profile_id' => ['required', 'exists:rt_profiles,id'],
            'family_card_number' => ResidentLetterProfile::familyCardNumberRules(required: true, unique: true),
            'house_number' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            ...ResidentLetterProfile::householdRecapValidationRules(),
            'phone' => PhoneNormalizer::validationRules(true),
            'whatsapp_notify' => ['boolean'],
            'document_kk' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'head_face_descriptor' => ['required', 'array', 'size:128'],
            'head_face_descriptor.*' => ['numeric'],
            'head_selfie_data' => ['required', 'string', 'max:700000'],
            'members' => ['required', 'array', 'min:1', 'max:'.$maxMembers],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/', 'distinct'],
            'members.*.birth_place' => ['required', 'string', 'max:100'],
            'members.*.birth_date' => ['required', 'date'],
            'members.*.gender' => ['required', 'string', Rule::in(['Laki-laki', 'Perempuan'])],
            'members.*.relationship' => ['nullable', 'string', 'max:30'],
            'members.*.document_id' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            ...$this->memberDemographicRules(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $raw
     * @return array<int, array<string, mixed>>
     */
    protected function normalizePendataanWargaMembers(array $raw): array
    {
        $raw = array_values($raw);
        $normalized = [];

        foreach ($raw as $i => $member) {
            $nik = preg_replace('/\D/', '', (string) ($member['nik'] ?? ''));
            if (strlen($nik) !== 16) {
                throw ValidationException::withMessages([
                    "members.{$i}.nik" => 'NIK harus 16 digit.',
                ]);
            }

            $relationship = $i === 0
                ? 'Kepala Keluarga'
                : ($member['relationship'] ?? 'Anggota Keluarga');

            $normalized[] = [
                'nik' => $nik,
                'name' => $member['name'],
                'birth_place' => $member['birth_place'],
                'birth_date' => $member['birth_date'],
                'gender' => $member['gender'],
                'relationship' => $relationship,
                'occupation' => $member['occupation'] ?? null,
                'education' => $member['education'] ?? null,
                'religion' => $member['religion'] ?? null,
                'marital_status' => $member['marital_status'] ?? null,
                'citizenship' => $member['citizenship'] ?? 'WNI',
            ];
        }

        return $normalized;
    }

    protected function memberIdentityDocumentType(?string $birthDate, int $index): string
    {
        $prefix = 'ktp';
        if ($birthDate) {
            $age = \Carbon\Carbon::parse($birthDate)->age;
            if ($age < 17) {
                $prefix = 'kia';
            }
        }

        return $prefix.'_a'.$index;
    }

    protected function mergePendataanFaceDescriptorInputs(Request $request): void
    {
        foreach (['head_face_descriptor'] as $field) {
            if (! is_string($request->input($field))) {
                continue;
            }

            $decoded = json_decode($request->input($field), true);
            if (is_array($decoded)) {
                $request->merge([$field => $decoded]);
            }
        }
    }

    protected function storeHeadSelfieDocument(Household $household, string $selfieDataUri): void
    {
        if (! preg_match('#^data:image/(jpeg|jpg|png);base64,#i', $selfieDataUri, $matches)) {
            throw ValidationException::withMessages([
                'head_selfie_data' => 'Format foto selfie tidak valid.',
            ]);
        }

        $raw = base64_decode(substr($selfieDataUri, strlen($matches[0])), true);
        if ($raw === false || strlen($raw) < 1000) {
            throw ValidationException::withMessages([
                'head_selfie_data' => 'Foto selfie tidak valid atau terlalu kecil.',
            ]);
        }

        $ext = strtolower($matches[1]) === 'png' ? 'png' : 'jpg';
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';

        $this->pendataanDocumentStorage->storeFromContents(
            $household,
            $raw,
            'selfie_kepala',
            'selfie-verifikasi-kepala.'.$ext,
            $mime,
        );
    }
}
