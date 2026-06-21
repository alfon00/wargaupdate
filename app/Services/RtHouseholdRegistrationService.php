<?php

namespace App\Services;

use App\Enums\DomicileStatus;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Support\PhoneNormalizer;
use App\Support\ResidentLetterProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RtHouseholdRegistrationService
{
    public function __construct(
        private readonly GuestResidentService $guestResidentService,
    ) {}

    /**
     * @return array{household: Household, members: \Illuminate\Support\Collection<int, Resident>, face_sync_warning: ?string}
     */
    public function register(Request $request, RtProfile $rt): array
    {
        if ($request->has('family_card_number')) {
            $request->merge([
                'family_card_number' => ResidentLetterProfile::normalizeFamilyCardNumber(
                    $request->input('family_card_number')
                ),
            ]);
        }

        $maxMembers = (int) config('kelurahan.pendataan_max_anggota', 50);

        $validated = $request->validate(
            $this->rules($maxMembers),
            [
                ...ResidentLetterProfile::familyCardNumberMessages(),
                ...ResidentLetterProfile::householdRecapMessages(),
                'members.*.nik.distinct' => 'NIK tiap anggota keluarga harus berbeda. Periksa kembali NIK yang sama.',
            ],
        );

        $members = $this->normalizeMembers($validated['members'] ?? []);

        foreach ($members as $index => $member) {
            if (! empty($member['nik'])) {
                $existing = $this->guestResidentService->findByNik($member['nik']);
                if ($existing && ! $existing->domicile_status?->isArchived()) {
                    throw ValidationException::withMessages([
                        "members.{$index}.nik" => 'NIK sudah terdaftar pada warga aktif.',
                    ]);
                }
            }
        }

        return DB::transaction(function () use ($validated, $members, $rt, $request) {
            $registrationType = count($members) > 1 ? 'keluarga' : 'perorangan';

            $household = Household::create([
                'rt_profile_id' => $rt->id,
                'family_card_number' => $validated['family_card_number'],
                'house_number' => $validated['house_number'] ?? null,
                'address' => $validated['address'],
                'status' => 'aktif',
                'registration_type' => $registrationType,
                'pendataan_category' => '',
                'status_rumah_tinggal' => $validated['status_rumah_tinggal'],
                'suku' => $validated['suku'],
                'kondisi_rumah_milik' => $validated['kondisi_rumah_milik'] ?? null,
            ]);

            $created = collect();
            $notifyEnabled = $request->boolean('whatsapp_notify', true);
            foreach ($members as $i => $member) {
                $isHead = $i === 0;
                $created->push(Resident::create([
                    'household_id' => $household->id,
                    'nik' => $member['nik'] ?? null,
                    'name' => $member['name'],
                    'phone' => $isHead ? ($validated['phone'] ?? null) : null,
                    'birth_place' => $member['birth_place'],
                    'birth_date' => $member['birth_date'],
                    'gender' => $member['gender'],
                    'is_head_of_family' => $isHead,
                    'relationship_to_head' => $member['relationship'],
                    'whatsapp_notify' => $notifyEnabled,
                    'domicile_status' => DomicileStatus::Aktif,
                    'verified_at' => now(),
                    ...ResidentLetterProfile::demographicAttributesFromInput($member),
                ]));
            }

            $faceSyncWarning = $this->guestResidentService->storePendataanDocuments($household, $request);

            return [
                'household' => $household->load('rtProfile'),
                'members' => $created,
                'face_sync_warning' => $faceSyncWarning,
            ];
        });
    }

    /** @return array<string, mixed> */
    private function rules(int $maxMembers): array
    {
        return [
            'family_card_number' => ResidentLetterProfile::familyCardNumberRules(required: true, unique: true),
            'house_number' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            ...ResidentLetterProfile::householdRecapValidationRules(),
            'phone' => PhoneNormalizer::validationRules(true),
            'whatsapp_notify' => ['boolean'],
            'document_kk' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'document_ktp' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'members' => ['required', 'array', 'min:1', 'max:'.$maxMembers],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/', 'distinct'],
            'members.*.birth_place' => ['required', 'string', 'max:100'],
            'members.*.birth_date' => ['required', 'date'],
            'members.*.gender' => ['required', 'string', Rule::in(['Laki-laki', 'Perempuan'])],
            'members.*.relationship' => ['nullable', 'string', 'max:30'],
            ...$this->memberDemographicRules(),
        ];
    }

    /** @return array<string, mixed> */
    private function memberDemographicRules(): array
    {
        return ResidentLetterProfile::demographicValidationRules('members.*');
    }

    /**
     * @param  array<int, array<string, mixed>>  $raw
     * @return array<int, array<string, mixed>>
     */
    private function normalizeMembers(array $raw): array
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
}
