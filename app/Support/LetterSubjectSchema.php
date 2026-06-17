<?php

namespace App\Support;

use App\Models\Resident;
use Illuminate\Validation\ValidationException;

final class LetterSubjectSchema
{
    public static function maxSubjects(): int
    {
        return max(1, (int) config('kelurahan.letter_max_subjects', 10));
    }

    /** @return array<string, mixed> */
    public static function validationRules(): array
    {
        $max = self::maxSubjects();

        return [
            'subject_count' => ['required', 'integer', 'min:1', 'max:'.$max],
            'subjects' => ['required', 'array'],
            'subjects.*.name' => ['required', 'string', 'max:255'],
            'subjects.*.nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/'],
            'subjects.*.document' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    /** @param  array{subject_count: int|string, subjects: list<array{name: string, nik: string}>}  $validated */
    public static function assertSubjectCountMatches(array $validated): void
    {
        $expected = (int) $validated['subject_count'];
        $actual = count($validated['subjects']);

        if ($actual !== $expected) {
            throw ValidationException::withMessages([
                'subjects' => 'Jumlah data orang tidak sesuai dengan pilihan jumlah orang.',
            ]);
        }
    }

    /**
     * @return list<array{name: string, nik: string, resident_id: int}>
     */
    public static function forPemohon(Resident $resident): array
    {
        return [[
            'name' => $resident->name,
            'nik' => $resident->nik,
            'resident_id' => $resident->id,
        ]];
    }

    /**
     * @param  list<array{name: string, nik: string}>  $subjects
     * @return list<array{name: string, nik: string, resident_id: int|null}>
     */
    public static function enrichWithResidentIds(array $subjects, Resident $pemohon): array
    {
        $pemohon->loadMissing('household.residents');
        $householdResidents = $pemohon->household?->residents ?? collect();

        return array_map(function (array $subject) use ($householdResidents): array {
            $nik = preg_replace('/\D/', '', $subject['nik']) ?? '';
            $match = $householdResidents->firstWhere('nik', $nik);

            return [
                'name' => $subject['name'],
                'nik' => $nik,
                'resident_id' => $match?->id,
            ];
        }, $subjects);
    }

    public static function documentTypeForIndex(int $index): string
    {
        return 'subject_'.$index;
    }

    public static function documentLabelForIndex(int $index, ?string $name = null): string
    {
        $label = 'KTP/KK orang '.($index + 1);

        if ($name) {
            $label .= ' — '.$name;
        }

        return $label;
    }
}
