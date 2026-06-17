<?php

namespace App\Support;

use App\Models\Resident;
use App\Support\SignatureStorage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ResidentLetterProfile
{
    /** @return list<string> */
    public static function residentAttributeKeys(): array
    {
        return ['occupation', 'education', 'religion', 'marital_status', 'citizenship'];
    }

    /** @return list<string> */
    public static function missingKeys(Resident $resident): array
    {
        $resident->loadMissing('household');
        $missing = [];

        if (! filled($resident->name)) {
            $missing[] = 'name';
        }
        if (! filled($resident->birth_place) || ! $resident->birth_date) {
            $missing[] = 'birth';
        }
        if (! filled($resident->gender)) {
            $missing[] = 'gender';
        }

        foreach (self::residentAttributeKeys() as $key) {
            if (! filled($resident->{$key})) {
                $missing[] = $key;
            }
        }

        $household = $resident->household;
        if (! filled($household?->address)) {
            $missing[] = 'address';
        }

        if (self::requiresFamilyCardNumber($resident) && ! filled($household?->family_card_number)) {
            $missing[] = 'family_card_number';
        }

        if (self::requiresNik($resident) && ! filled($resident->nik)) {
            $missing[] = 'nik';
        }

        if (! filled($household?->status_rumah_tinggal)) {
            $missing[] = 'status_rumah_tinggal';
        }

        if (! filled($household?->suku)) {
            $missing[] = 'suku';
        }

        if (HouseholdHousingOptions::requiresKondisiRumahMilik($household?->status_rumah_tinggal) && ! filled($household?->kondisi_rumah_milik)) {
            $missing[] = 'kondisi_rumah_milik';
        }

        return $missing;
    }

    public static function isComplete(Resident $resident): bool
    {
        return self::missingKeys($resident) === [];
    }

    public static function requiresFamilyCardNumber(Resident $resident): bool
    {
        return ($resident->household?->pendataan_category ?? '') !== 'belum_identitas';
    }

    public static function requiresNik(Resident $resident): bool
    {
        return ($resident->household?->pendataan_category ?? '') !== 'belum_identitas';
    }

    public static function requiresKondisiRumahMilik(?string $statusRumahTinggal): bool
    {
        if (! filled($statusRumahTinggal)) {
            return false;
        }

        return str_contains(mb_strtolower($statusRumahTinggal), 'milik sendiri');
    }

    /** @return array<string, mixed> */
    public static function householdRecapValidationRules(): array
    {
        return [
            'status_rumah_tinggal' => ['required', 'string', 'max:50'],
            'suku' => ['required', 'string', 'max:100'],
            'kondisi_rumah_milik' => [
                Rule::requiredIf(fn () => self::requiresKondisiRumahMilik(request()->input('status_rumah_tinggal'))),
                'nullable',
                'in:layak,tidak_layak',
            ],
        ];
    }

    /** @return array<string, string> */
    public static function householdRecapMessages(): array
    {
        return [
            'status_rumah_tinggal.required' => 'Status rumah tinggal wajib diisi.',
            'status_rumah_tinggal.in' => 'Pilih status rumah tinggal yang valid.',
            'suku.required' => 'Suku wajib diisi.',
            'kondisi_rumah_milik.required' => 'Kondisi rumah milik sendiri wajib diisi.',
            'kondisi_rumah_milik.in' => 'Pilih layak atau tidak layak.',
        ];
    }

    /** @return array<string, mixed> */
    public static function householdFormValidationRules(bool $requireFamilyCard = true): array
    {
        return array_merge([
            'family_card_number' => self::familyCardNumberRules($requireFamilyCard),
            'house_number' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string'],
        ], self::householdRecapValidationRules());
    }

    /** @return array<string, string> */
    public static function householdFormValidationMessages(): array
    {
        return array_merge(
            self::familyCardNumberMessages(),
            self::householdRecapMessages(),
        );
    }

    /** @return list<string> */
    public static function householdFormFieldKeys(): array
    {
        return [
            'family_card_number',
            'house_number',
            'address',
            'status_rumah_tinggal',
            'suku',
            'kondisi_rumah_milik',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function householdFieldsFromValidated(array $validated): array
    {
        $fields = array_intersect_key(
            $validated,
            array_flip(self::householdFormFieldKeys()),
        );

        if (isset($fields['status_rumah_tinggal'])) {
            $fields['status_rumah_tinggal'] = HouseholdHousingOptions::normalizeStatus($fields['status_rumah_tinggal'])
                ?? $fields['status_rumah_tinggal'];
        }

        return $fields;
    }

    /** @return array<string, string> */
    public static function missingLabels(Resident $resident): array
    {
        $labels = [
            'name' => 'Nama lengkap',
            'birth' => 'Tempat & tanggal lahir',
            'gender' => 'Jenis kelamin',
            'occupation' => 'Pekerjaan',
            'education' => 'Pendidikan',
            'religion' => 'Agama',
            'marital_status' => 'Status perkawinan',
            'citizenship' => 'Kewarganegaraan',
            'address' => 'Alamat tempat tinggal',
            'family_card_number' => 'Nomor KK',
            'nik' => 'NIK',
            'status_rumah_tinggal' => 'Status rumah tinggal',
            'suku' => 'Suku',
            'kondisi_rumah_milik' => 'Kondisi rumah milik sendiri',
        ];

        $out = [];
        foreach (self::missingKeys($resident) as $key) {
            $out[$key] = $labels[$key] ?? $key;
        }

        return $out;
    }

    public static function assertComplete(Resident $resident): void
    {
        if (self::isComplete($resident)) {
            return;
        }

        $list = implode(', ', array_values(self::missingLabels($resident)));

        throw ValidationException::withMessages([
            'letter_profile' => 'Data kependudukan belum lengkap untuk surat pengantar. Lengkapi: '.$list.'. Gunakan menu Pendataan atau Pembaruan data di portal layanan.',
        ]);
    }

    /** @return list<mixed> */
    public static function familyCardNumberRules(bool $required = false, bool $unique = false): array
    {
        $rules = ['string', 'size:16', 'regex:/^\d{16}$/'];
        array_unshift($rules, $required ? 'required' : 'nullable');
        if ($unique) {
            $rules[] = 'unique:households,family_card_number';
        }

        return $rules;
    }

    /** @return array<string, string> */
    public static function familyCardNumberMessages(): array
    {
        return [
            'family_card_number.size' => 'Nomor KK harus 16 digit angka.',
            'family_card_number.regex' => 'Nomor KK harus 16 digit angka.',
            'family_card_number.unique' => 'Nomor KK sudah terdaftar. Periksa kembali atau hubungi pengurus RT.',
        ];
    }

    public static function normalizeFamilyCardNumber(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        return $digits !== '' ? $digits : null;
    }

    /** @return array<string, mixed> */
    public static function demographicValidationRules(string $prefix = ''): array
    {
        $p = $prefix !== '' ? $prefix.'.' : '';

        return [
            "{$p}occupation" => ['required', 'string', 'max:100'],
            "{$p}education" => ['required', 'string', 'max:100'],
            "{$p}religion" => ['required', 'string', 'max:30'],
            "{$p}marital_status" => ['required', 'string', 'max:30'],
            "{$p}citizenship" => ['required', 'string', 'max:30'],
        ];
    }

    /** @param  array<string, mixed>  $data */
    public static function demographicAttributesFromInput(array $data): array
    {
        return [
            'occupation' => $data['occupation'] ?? null,
            'education' => $data['education'] ?? null,
            'religion' => $data['religion'] ?? null,
            'marital_status' => $data['marital_status'] ?? null,
            'citizenship' => $data['citizenship'] ?? 'WNI',
        ];
    }

    /** @return array<string, mixed> */
    public static function rtChairSignatureRules(): array
    {
        return [
            'signature_data' => [
                'required',
                'string',
                'max:500000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (SignatureStorage::isBlank(is_string($value) ? $value : null)) {
                        $fail('Tanda tangan Ketua RT wajib diisi pada kanvas sebelum menghapus permanen.');
                    }
                },
            ],
        ];
    }

    /** @return array<string, string> */
    public static function rtChairSignatureMessages(): array
    {
        return [
            'signature_data.required' => 'Tanda tangan Ketua RT wajib diisi pada kanvas sebelum menghapus permanen.',
        ];
    }
}
