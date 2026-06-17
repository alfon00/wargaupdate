<?php

namespace App\Support;

use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;

final class RtPopulationAnalytics
{
    private const EDUCATION_BUCKETS = ['TK', 'SD', 'SLTP', 'SLTA', 'PT'];

    /** @return array{population: array<string, int|float>, education: array<string, int>, gender: array<string, int|float>} */
    public static function forRtProfile(RtProfile $rt): array
    {
        $residents = Resident::forRtProfile($rt)
            ->domiciledActive()
            ->get(['gender', 'education', 'household_id']);

        $households = Household::forRtProfile($rt)
            ->whereHas('residents', fn ($q) => $q->domiciledActive())
            ->count();

        return self::buildAnalytics($residents, $households);
    }

    /** @return array{population: array<string, int|float>, education: array<string, int>, gender: array<string, int|float>} */
    public static function forKelurahan(): array
    {
        $residents = Resident::query()
            ->domiciledActive()
            ->whereHas('household', fn ($q) => $q->whereNotNull('rt_profile_id'))
            ->get(['gender', 'education', 'household_id']);

        $households = Household::query()
            ->whereNotNull('rt_profile_id')
            ->whereHas('residents', fn ($q) => $q->domiciledActive())
            ->count();

        return self::buildAnalytics($residents, $households);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Resident>  $residents
     * @return array{population: array<string, int|float>, education: array<string, int>, gender: array<string, int|float>}
     */
    private static function buildAnalytics($residents, int $households): array
    {
        $total = $residents->count();
        $classified = 0;
        $genderCounts = ['L' => 0, 'P' => 0, 'unknown' => 0];
        $educationCounts = array_fill_keys(self::EDUCATION_BUCKETS, 0);
        $educationCounts['other'] = 0;

        foreach ($residents as $resident) {
            $genderKey = GenderNormalizer::key($resident->gender);
            if ($genderKey !== null) {
                $classified++;
                $genderCounts[$genderKey]++;
            } else {
                $genderCounts['unknown']++;
            }

            $bucket = self::educationBucket($resident->education);
            $educationCounts[$bucket]++;
        }

        $knownGender = $genderCounts['L'] + $genderCounts['P'];
        $malePercent = $knownGender > 0
            ? round($genderCounts['L'] / $knownGender * 100, 1)
            : 0.0;
        $femalePercent = $knownGender > 0
            ? round($genderCounts['P'] / $knownGender * 100, 1)
            : 0.0;

        $classifiedPercent = $total > 0
            ? round($classified / $total * 100, 1)
            : 0.0;

        $maxEducation = max(
            $educationCounts['TK'],
            $educationCounts['SD'],
            $educationCounts['SLTP'],
            $educationCounts['SLTA'],
            $educationCounts['PT'],
            0,
        );

        return [
            'population' => [
                'total' => $total,
                'households' => $households,
                'classified' => $classified,
                'unclassified' => $total - $classified,
                'classified_percent' => $classifiedPercent,
            ],
            'education' => array_merge($educationCounts, ['max' => $maxEducation]),
            'gender' => [
                'L' => $genderCounts['L'],
                'P' => $genderCounts['P'],
                'unknown' => $genderCounts['unknown'],
                'known' => $knownGender,
                'male_percent' => $malePercent,
                'female_percent' => $femalePercent,
            ],
        ];
    }

    /** @return array{rows: array<int, array<string, int>>, totals: array<string, int>, highlight_row: ?int} */
    public static function monographTable(?RtProfile $currentRt = null): array
    {
        $rows = [];
        $totals = self::emptyMonographRow();

        for ($i = 1; $i <= 8; $i++) {
            $rtNumber = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $canonicalId = RtProfile::canonicalProfileIdForRtNumber($rtNumber);
            $profile = $canonicalId ? RtProfile::find($canonicalId) : null;
            $rows[$i] = $profile
                ? self::monographRowFromAnalytics(self::forRtProfile($profile))
                : self::emptyMonographRow();

            foreach ($rows[$i] as $key => $value) {
                $totals[$key] += $value;
            }
        }

        $highlightRow = null;
        if ($currentRt) {
            $index = (int) ltrim(RtProfile::normalizeRtNumber($currentRt->rt_number), '0');
            if ($index >= 1 && $index <= 8) {
                $highlightRow = $index;
            }
        }

        return [
            'rows' => $rows,
            'totals' => $totals,
            'highlight_row' => $highlightRow,
        ];
    }

    /** @return array<string, int> */
    private static function emptyMonographRow(): array
    {
        return [
            'L' => 0,
            'P' => 0,
            'jiwa' => 0,
            'kk' => 0,
            'TK' => 0,
            'SD' => 0,
            'SLTA' => 0,
            'SLTP' => 0,
            'PT' => 0,
            'jumlah' => 0,
        ];
    }

    /** @param array{population: array<string, int|float>, education: array<string, int>, gender: array<string, int|float>} $analytics */
    private static function monographRowFromAnalytics(array $analytics): array
    {
        $education = $analytics['education'];

        return [
            'L' => (int) $analytics['gender']['L'],
            'P' => (int) $analytics['gender']['P'],
            'jiwa' => (int) $analytics['population']['total'],
            'kk' => (int) $analytics['population']['households'],
            'TK' => (int) $education['TK'],
            'SD' => (int) $education['SD'],
            'SLTA' => (int) $education['SLTA'],
            'SLTP' => (int) $education['SLTP'],
            'PT' => (int) $education['PT'],
            'jumlah' => (int) $education['TK']
                + (int) $education['SD']
                + (int) $education['SLTP']
                + (int) $education['SLTA']
                + (int) $education['PT'],
        ];
    }

    public static function educationBucket(?string $education): string
    {
        if (! filled($education)) {
            return 'other';
        }

        $normalized = mb_strtolower(trim($education));

        return match (true) {
            $normalized === 'tk' => 'TK',
            $normalized === 'sd' => 'SD',
            in_array($normalized, ['smp', 'sltp'], true) => 'SLTP',
            in_array($normalized, ['sma/smk', 'sma', 'smk', 'slta'], true) => 'SLTA',
            in_array($normalized, ['diploma', 's1', 's2', 's3', 'pt'], true) => 'PT',
            default => 'other',
        };
    }
}
