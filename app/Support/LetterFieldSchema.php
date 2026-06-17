<?php

namespace App\Support;

use App\Models\Application;
use App\Models\Resident;

class LetterFieldSchema
{
    /** @return list<array{key: string, label: string, type: string, required: bool}> */
    public static function forServiceCode(string $code): array
    {
        $common = config('kelurahan.letter_fields._common', []);
        $specific = config("kelurahan.letter_fields.{$code}", []);

        return array_merge($common, $specific);
    }

    /** @return list<string> */
    public static function autoFilledKeys(): array
    {
        return array_values(array_filter(
            array_column(config('kelurahan.letter_fields._common', []), 'key'),
            static fn (string $key): bool => $key !== 'keperluan',
        ));
    }

    /** @return list<string> */
    public static function manualComposeKeys(): array
    {
        return ['keperluan'];
    }

    /** @return list<array{key: string, label: string, type: string, required: bool}> */
    public static function serviceSpecificFields(string $code): array
    {
        $excludeKeys = array_merge(self::autoFilledKeys(), self::manualComposeKeys());

        return array_values(array_filter(
            self::forServiceCode($code),
            static fn (array $field): bool => ! in_array($field['key'], $excludeKeys, true),
        ));
    }

    /** @return array<string, string> */
    public static function savedFieldValuesFromApplication(Application $application): array
    {
        $formData = $application->form_data ?? [];
        $saved = $formData['letter']['fields'] ?? [];

        if (! is_array($saved)) {
            $saved = [];
        }

        foreach (['nama_usaha', 'jenis_usaha', 'alamat_usaha'] as $key) {
            if (! filled($saved[$key] ?? null) && filled($formData[$key] ?? null)) {
                $saved[$key] = (string) $formData[$key];
            }
        }

        return array_map(
            static fn ($value): string => is_string($value) ? $value : (string) $value,
            $saved,
        );
    }

    /** @return list<string> */
    public static function applicantFieldKeys(): array
    {
        return array_values(array_filter(
            self::autoFilledKeys(),
            static fn (string $key): bool => $key !== 'keperluan',
        ));
    }

    /** @return array<string, string> */
    public static function valuesFromResident(Resident $resident, Application $application): array
    {
        $application->loadMissing(['assignedRtProfile']);
        $resident->loadMissing('household.rtProfile');

        $household = $resident->household;
        $rt = $application->resolvedRtProfile() ?? $household?->rtProfile;

        $kk = $household?->family_card_number;
        $noKtpKk = $resident->nik ?? '';
        if ($kk) {
            $noKtpKk = trim($noKtpKk.' / KK: '.$kk);
        }

        $rtLabel = $rt?->displayName() ?? 'RT';
        $rwLabel = $rt?->rw_number ? 'RW '.$rt->rw_number : '—';

        return [
            'nama' => $resident->name ?? '',
            'nik' => $resident->nik ?? '',
            'ttl' => $resident->birthPlaceDate(),
            'jenis_kelamin' => $resident->gender ?? '',
            'pekerjaan' => $resident->occupation ?? '',
            'no_ktp_kk' => $noKtpKk,
            'kewarganegaraan' => $resident->citizenship ?? 'WNI',
            'pendidikan' => $resident->education ?? '',
            'agama' => $resident->religion ?? '',
            'status_perkawinan' => $resident->marital_status ?? '',
            'alamat' => $resident->fullAddress(),
            'rt_rw' => $rtLabel.' / '.$rwLabel,
        ];
    }

    /** @return array<string, string> */
    public static function defaultValues(Application $application): array
    {
        $application->loadMissing(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType', 'generatedLetter']);

        $resident = $application->resident;
        $household = $resident?->household;
        $rt = $application->resolvedRtProfile() ?? $household?->rtProfile;

        $saved = self::savedFieldValuesFromApplication($application);

        $personValues = $resident
            ? self::valuesFromResident($resident, $application)
            : array_fill_keys(self::applicantFieldKeys(), '');

        $rtLabel = $rt?->displayName() ?? 'RT';

        $defaults = array_merge($personValues, [
            'keperluan' => self::buildKeperluanDefault($application),
            'nama_usaha' => '',
            'jenis_usaha' => '',
            'alamat_usaha' => $household?->address ?? '',
            'ketua_rt' => $rt?->ketua_rt ?? $rt?->primaryKetua()?->name ?? '',
            'ketua_rw' => $rt?->ketua_rw ?? '',
            'rt' => $rtLabel,
            'rw' => $rt?->rw_number ?? '',
            'kelurahan' => $rt?->kelurahan ?? config('kelurahan.nama'),
            'kecamatan' => $rt?->kecamatan ?? config('kelurahan.distrik'),
            'distrik' => config('kelurahan.distrik'),
            'kabupaten' => $rt?->kota ?? config('kelurahan.kabupaten'),
            'provinsi' => $rt?->provinsi ?? config('kelurahan.provinsi'),
            'alamat_kantor' => $rt?->alamat_kantor ?? '',
        ]);

        if ($application->generatedLetter?->letter_number) {
            $defaults['nomor_surat'] = $application->generatedLetter->letter_number;
        }

        foreach (self::forServiceCode($application->serviceType->code) as $field) {
            $key = $field['key'];
            if (! array_key_exists($key, $defaults)) {
                $defaults[$key] = '';
            }
        }

        $merged = array_merge($defaults, $saved);

        if (! filled($merged['alamat_usaha'] ?? null)) {
            $merged['alamat_usaha'] = $household?->address ?? '';
        }

        if (! filled($merged['keperluan'] ?? null)) {
            $merged['keperluan'] = self::buildKeperluanDefault($application);
        }

        return $merged;
    }

    public static function buildKeperluanDefault(Application $application): string
    {
        $application->loadMissing('serviceType');

        $purpose = trim((string) ($application->purpose ?? ''));

        if ($application->serviceType?->code !== 'surat_usaha') {
            return $purpose;
        }

        $saved = self::savedFieldValuesFromApplication($application);

        $lines = array_filter([
            $purpose !== '' ? $purpose : 'Surat Keterangan Usaha',
            filled($saved['nama_usaha'] ?? null) ? 'Nama usaha: '.$saved['nama_usaha'] : null,
            filled($saved['jenis_usaha'] ?? null) ? 'Jenis usaha: '.$saved['jenis_usaha'] : null,
            filled($saved['alamat_usaha'] ?? null) ? 'Alamat usaha: '.$saved['alamat_usaha'] : null,
        ]);

        return implode("\n", $lines);
    }

    /** @param  array<string, mixed>  $input */
    public static function validate(string $serviceCode, array $input): array
    {
        $rules = [];
        foreach (self::forServiceCode($serviceCode) as $field) {
            $key = $field['key'];
            $rules["fields.{$key}"] = $field['required']
                ? ['required', 'string', 'max:2000']
                : ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    public static function logoImgTag(Application $application): string
    {
        $application->loadMissing(['resident.household.rtProfile', 'assignedRtProfile']);
        $rt = $application->resolvedRtProfile() ?? $application->resident?->household?->rtProfile;
        if (! $rt) {
            return '';
        }

        $url = $rt->publicPhotoUrl();
        if (! $url) {
            return '';
        }

        $src = e($url);

        return '<img src="'.$src.'" alt="Logo RT" style="max-height:64px;max-width:72px">';
    }
}
