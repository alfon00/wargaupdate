<?php

namespace App\Http\Controllers\Kelurahan;

use App\Enums\DomicileStatus;
use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Support\HouseholdHousingOptions;
use App\Support\ResidentLetterProfile;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PopulationController extends Controller
{
    public const AGE_BUCKETS = [
        [0, 5],
        [6, 10],
        [11, 15],
        [16, 20],
        [21, 25],
        [26, 30],
        [31, 40],
        [41, 50],
        [51, 60],
        [61, 80],
        [81, 120],
    ];

    public function index(Request $request): View
    {
        $rtProfiles = RtProfile::forPublicSelect()->get();
        $query = $this->filteredHouseholdsQuery($request);
        $households = (clone $query)->paginate(20)->withQueryString();
        $rows = $this->buildRows($households);
        $rtSummaries = $this->buildRtSummaries($request);
        $pageTotals = $this->aggregatePageTotals($rows);

        $statsQuery = $this->filteredHouseholdsQuery($request);
        $householdIds = $statsQuery->pluck('id');
        $selectedRt = $request->filled('rt_profile_id')
            ? $rtProfiles->firstWhere('id', (int) $request->input('rt_profile_id'))
            : null;

        return view('kelurahan.population.index', [
            'households' => $households,
            'rows' => $rows,
            'rtSummaries' => $rtSummaries,
            'pageTotals' => $pageTotals,
            'rtProfiles' => $rtProfiles,
            'selectedRt' => $selectedRt,
            'showRtSummary' => ! $request->filled('rt_profile_id'),
            'stats' => [
                'households' => $householdIds->count(),
                'residents' => $householdIds->isEmpty()
                    ? 0
                    : Resident::whereIn('household_id', $householdIds)->domiciledActive()->count(),
                'rt_count' => $statsQuery->distinct('rt_profile_id')->count('rt_profile_id'),
            ],
            'buckets' => self::AGE_BUCKETS,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $households = $this->filteredHouseholdsQuery($request)->get();
        $rows = $this->buildRowsFromCollection($households);
        $filename = 'rekap-penduduk-'.now('Asia/Jayapura')->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            $header = [
                'RT', 'No', 'Nama KK', 'Anggota aktif', 'Total L', 'Total P', 'Tdk terklasifikasi',
                'Status KK', 'Agama', 'Pekerjaan',
            ];
            foreach (self::AGE_BUCKETS as [$min, $max]) {
                $label = $this->bucketLabel($min, $max);
                $header[] = "{$label} L";
                $header[] = "{$label} P";
            }
            $header[] = 'Status rumah tinggal';
            $header[] = 'Kondisi rumah milik';
            $header[] = 'Suku';
            $header[] = 'Alamat tinggal';

            fputcsv($out, $header, ';');

            foreach ($rows as $i => $row) {
                $household = $row['household'];
                $line = [
                    $row['rt'],
                    (string) ($i + 1),
                    $row['head_name'],
                    (string) $row['active_count'],
                    (string) $row['totals']['L'],
                    (string) $row['totals']['P'],
                    (string) $row['unclassified'],
                    $this->householdStatusLabel($household->status),
                    $row['religion'],
                    $row['occupation'],
                ];
                foreach (self::AGE_BUCKETS as [$min, $max]) {
                    $key = $this->bucketKey($min, $max);
                    $line[] = (string) ($row['buckets'][$key]['L'] ?? 0);
                    $line[] = (string) ($row['buckets'][$key]['P'] ?? 0);
                }
                $line[] = $household->status_rumah_tinggal ?? '—';
                $line[] = $this->kondisiRumahLabel($household->kondisi_rumah_milik);
                $line[] = $household->suku ?? '—';
                $line[] = $row['address'] ?: '—';

                fputcsv($out, $line, ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** @return Builder<Household> */
    private function filteredHouseholdsQuery(Request $request): Builder
    {
        $query = Household::with(['rtProfile', 'residents'])
            ->orderBy('rt_profile_id')
            ->latest('id');

        if ($request->filled('rt_profile_id')) {
            $rt = RtProfile::find((int) $request->rt_profile_id);
            if ($rt) {
                $profileIds = RtProfile::profileIdsForRtNumber($rt->rt_number);
                $query->whereIn('rt_profile_id', $profileIds);
            }
        }

        if ($request->filled('q')) {
            $term = '%'.$request->q.'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('address', 'like', $term)
                    ->orWhere('suku', 'like', $term)
                    ->orWhereHas('residents', function ($r) use ($term) {
                        $r->where('is_head_of_family', true)
                            ->where('name', 'like', $term);
                    });
            });
        }

        return $query;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildRows(LengthAwarePaginator $households): array
    {
        return $this->buildRowsFromCollection($households->getCollection());
    }

    /** @return array<int, array<string, mixed>> */
    private function buildRowsFromCollection(Collection $households): array
    {
        $rows = [];

        foreach ($households as $household) {
            $head = $household->residents->firstWhere('is_head_of_family', true);
            $summary = $this->summarizeHouseholdResidents($household->residents);

            $recap = $this->assessRecapCompleteness($household, $head);

            $rows[] = [
                'rt' => $household->rtProfile?->displayName() ?? 'RT —',
                'household' => $household,
                'head_name' => $head?->name ?? '—',
                'religion' => filled($head?->religion) ? $head->religion : '—',
                'occupation' => filled($head?->occupation) ? $head->occupation : '—',
                'active_count' => $summary['active_count'],
                'totals' => $summary['totals'],
                'unclassified' => $summary['unclassified'],
                'completeness' => $summary['completeness'],
                'buckets' => $summary['buckets'],
                'address' => trim(($household->address ?? '').' No. '.($household->house_number ?? '')),
                'recap_incomplete' => $recap['incomplete'],
                'missing_recap_labels' => $recap['missing_labels'],
            ];
        }

        return $rows;
    }

    /** @return list<array<string, mixed>> */
    private function buildRtSummaries(Request $request): array
    {
        $grouped = [];

        foreach ($this->filteredHouseholdsQuery($request)->get() as $household) {
            $rtNumber = $household->rtProfile?->rt_number ?? '—';
            if (! isset($grouped[$rtNumber])) {
                $grouped[$rtNumber] = [
                    'rt_profile_id' => $household->rt_profile_id,
                    'rt_label' => $household->rtProfile?->displayName() ?? 'RT —',
                    'households' => 0,
                    'residents' => 0,
                    'totals' => ['L' => 0, 'P' => 0],
                    'unclassified' => 0,
                    'buckets' => $this->emptyBucketCounts(),
                ];
            }

            $grouped[$rtNumber]['households']++;
            $summary = $this->summarizeHouseholdResidents($household->residents);
            $grouped[$rtNumber]['residents'] += $summary['active_count'];
            $grouped[$rtNumber]['totals']['L'] += $summary['totals']['L'];
            $grouped[$rtNumber]['totals']['P'] += $summary['totals']['P'];
            $grouped[$rtNumber]['unclassified'] += $summary['unclassified'];
            $this->mergeBucketCounts($grouped[$rtNumber]['buckets'], $summary['buckets']);
        }

        ksort($grouped, SORT_NATURAL);

        return array_values($grouped);
    }

    /**
     * @return array{incomplete: bool, missing_labels: list<string>}
     */
    private function assessRecapCompleteness(Household $household, ?Resident $head): array
    {
        $missing = [];

        if (! $head) {
            $missing[] = 'Kepala keluarga belum ditetapkan';
        } else {
            if (! filled($head->religion)) {
                $missing[] = 'Agama kepala keluarga';
            }
            if (! filled($head->occupation)) {
                $missing[] = 'Pekerjaan kepala keluarga';
            }
        }

        if (! filled($household->status_rumah_tinggal)) {
            $missing[] = 'Status rumah tinggal';
        }

        if (! filled($household->suku)) {
            $missing[] = 'Suku';
        }

        if (ResidentLetterProfile::requiresKondisiRumahMilik($household->status_rumah_tinggal)
            && ! filled($household->kondisi_rumah_milik)) {
            $missing[] = 'Kondisi rumah milik sendiri';
        }

        return [
            'incomplete' => $missing !== [],
            'missing_labels' => $missing,
        ];
    }

    /**
     * @param  Collection<int, Resident>  $residents
     * @return array{
     *     active_count: int,
     *     totals: array{L: int, P: int},
     *     unclassified: int,
     *     completeness: array{missing_birth: int, missing_gender: int},
     *     buckets: array<string, array{L: int, P: int}>
     * }
     */
    private function summarizeHouseholdResidents(Collection $residents): array
    {
        $buckets = $this->emptyBucketCounts();
        $totals = ['L' => 0, 'P' => 0];
        $unclassified = 0;
        $missingBirth = 0;
        $missingGender = 0;
        $activeCount = 0;

        foreach ($residents as $resident) {
            if ($resident->domicile_status !== DomicileStatus::Aktif) {
                continue;
            }

            $activeCount++;
            $age = $this->residentAge($resident);
            $genderKey = $this->genderKey($resident->gender);

            if (! $resident->birth_date || $age === null) {
                $missingBirth++;
            }
            if ($genderKey === null) {
                $missingGender++;
            }

            if ($age === null || $genderKey === null) {
                $unclassified++;

                continue;
            }

            $bucketKey = $this->ageBucketKey($age);
            if ($bucketKey === null) {
                $unclassified++;

                continue;
            }

            $buckets[$bucketKey][$genderKey]++;
            $totals[$genderKey]++;
        }

        return [
            'active_count' => $activeCount,
            'totals' => $totals,
            'unclassified' => $unclassified,
            'completeness' => [
                'missing_birth' => $missingBirth,
                'missing_gender' => $missingGender,
            ],
            'buckets' => $buckets,
        ];
    }

    /** @param  array<int, array<string, mixed>>  $rows */
    private function aggregatePageTotals(array $rows): array
    {
        $totals = [
            'households' => count($rows),
            'active_count' => 0,
            'totals' => ['L' => 0, 'P' => 0],
            'unclassified' => 0,
            'buckets' => $this->emptyBucketCounts(),
        ];

        foreach ($rows as $row) {
            $totals['active_count'] += $row['active_count'];
            $totals['totals']['L'] += $row['totals']['L'];
            $totals['totals']['P'] += $row['totals']['P'];
            $totals['unclassified'] += $row['unclassified'];
            $this->mergeBucketCounts($totals['buckets'], $row['buckets']);
        }

        return $totals;
    }

    /** @param  array<string, array{L:int, P:int}>  $target */
    private function mergeBucketCounts(array &$target, array $source): void
    {
        foreach ($source as $key => $counts) {
            $target[$key]['L'] += $counts['L'];
            $target[$key]['P'] += $counts['P'];
        }
    }

    /** @return array<string, array{L:int, P:int}> */
    private function emptyBucketCounts(): array
    {
        $out = [];
        foreach (self::AGE_BUCKETS as [$min, $max]) {
            $out[$this->bucketKey($min, $max)] = ['L' => 0, 'P' => 0];
        }

        return $out;
    }

    private function bucketKey(int $min, int $max): string
    {
        return "{$min}-{$max}";
    }

    public static function bucketLabel(int $min, int $max): string
    {
        if ($min === 81 && $max >= 120) {
            return '81+';
        }

        return "{$min}–{$max}";
    }

    public static function householdStatusLabel(?string $status): string
    {
        return match ($status) {
            'aktif' => 'Aktif',
            'nonaktif' => 'Nonaktif',
            default => $status ?: '—',
        };
    }

    private function kondisiRumahLabel(?string $value): string
    {
        return HouseholdHousingOptions::kondisiLabel($value);
    }

    private function residentAge(Resident $resident): ?int
    {
        if (! $resident->birth_date) {
            return null;
        }

        try {
            return Carbon::parse($resident->birth_date)->age;
        } catch (\Throwable) {
            return null;
        }
    }

    private function genderKey(?string $gender): ?string
    {
        if (! $gender) {
            return null;
        }

        $g = mb_strtolower(trim($gender));
        if ($g === 'laki-laki' || $g === 'l' || $g === 'pria') {
            return 'L';
        }
        if ($g === 'perempuan' || $g === 'p' || $g === 'wanita') {
            return 'P';
        }

        return null;
    }

    private function ageBucketKey(int $age): ?string
    {
        foreach (self::AGE_BUCKETS as [$min, $max]) {
            if ($age >= $min && $age <= $max) {
                return $this->bucketKey($min, $max);
            }
        }

        return null;
    }
}
