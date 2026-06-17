<?php

namespace App\Support;

use App\Models\Application;
use App\Models\Resident;

class ResidentProfileDisplay
{
    /** @return list<array{key: string, label: string}> */
    public static function standardFields(): array
    {
        return [
            ['key' => 'nama', 'label' => 'Nama lengkap'],
            ['key' => 'nik', 'label' => 'NIK'],
            ['key' => 'ttl', 'label' => 'Tempat, tanggal lahir'],
            ['key' => 'pekerjaan', 'label' => 'Pekerjaan'],
            ['key' => 'agama', 'label' => 'Agama'],
            ['key' => 'status_perkawinan', 'label' => 'Status perkawinan'],
            ['key' => 'kewarganegaraan', 'label' => 'Kewarganegaraan'],
            ['key' => 'alamat', 'label' => 'Alamat tempat tinggal'],
        ];
    }

    /** @return array<string, string> */
    public static function fromResident(Resident $resident): array
    {
        $resident->loadMissing('household.rtProfile');

        return [
            'nama' => $resident->name ?? '—',
            'nik' => $resident->nik ?: '—',
            'ttl' => $resident->birthPlaceDate() ?: '—',
            'pekerjaan' => $resident->occupation ?: '—',
            'agama' => $resident->religion ?: '—',
            'status_perkawinan' => $resident->marital_status ?: '—',
            'kewarganegaraan' => $resident->citizenship ?: '—',
            'alamat' => filled(trim($resident->fullAddress())) ? $resident->fullAddress() : '—',
        ];
    }

    /** @return array<string, string> */
    public static function fromApplication(Application $application): array
    {
        if ($application->resident) {
            return self::fromResident($application->resident);
        }

        $snapshot = $application->applicantSnapshot();
        if (! $snapshot) {
            return self::emptyFields($application->applicantName());
        }

        $place = $snapshot['birth_place'] ?? '-';
        $date = filled($snapshot['birth_date'] ?? null)
            ? \Illuminate\Support\Carbon::parse($snapshot['birth_date'])->translatedFormat('d F Y')
            : '-';

        $address = $snapshot['address'] ?? '—';
        if (filled($snapshot['house_number'] ?? null)) {
            $address = trim($address.' No. '.$snapshot['house_number']);
        }

        return [
            'nama' => $snapshot['name'] ?? 'Warga (data dihapus)',
            'nik' => $snapshot['nik'] ?? '—',
            'ttl' => $place.', '.$date,
            'pekerjaan' => $snapshot['occupation'] ?? '—',
            'agama' => $snapshot['religion'] ?? '—',
            'status_perkawinan' => $snapshot['marital_status'] ?? '—',
            'kewarganegaraan' => $snapshot['citizenship'] ?? '—',
            'alamat' => $address ?: '—',
        ];
    }

    /** @return array<string, string> */
    protected static function emptyFields(string $name): array
    {
        $empty = array_fill_keys(array_column(self::standardFields(), 'key'), '—');
        $empty['nama'] = $name;

        return $empty;
    }
}
