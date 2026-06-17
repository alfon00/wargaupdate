<?php

namespace App\Services;

use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class ResidentDataIndexService
{
    public const HOUSEHOLDS_PER_PAGE = 15;

    public const RESIDENTS_PER_PAGE = 20;

    /** @var list<array{value: string, label: string}> */
    public const KATEGORI_OPTIONS = [
        ['value' => 'semua', 'label' => 'Semua'],
        ['value' => 'entri_rt', 'label' => 'Entri RT'],
        ['value' => 'warga_baru', 'label' => 'Warga baru'],
        ['value' => 'warga_pindah', 'label' => 'Warga pindah'],
        ['value' => 'belum_identitas', 'label' => 'Belum identitas'],
    ];

    /** @return array{households: int, residents_active: int, residents_archived: int} */
    public function stats(?RtProfile $rt): array
    {
        if ($rt) {
            return [
                'households' => Household::forRtProfile($rt)->whereHas('residents')->count(),
                'residents_active' => Resident::forRtProfile($rt)->domiciledActive()->count(),
                'residents_archived' => Resident::forRtProfile($rt)->domiciledArchived()->count(),
            ];
        }

        $householdQuery = Household::query()
            ->whereIn('rt_profile_id', $this->inaugaProfileIds())
            ->whereHas('residents');
        $householdIds = (clone $householdQuery)->pluck('id');

        return [
            'households' => $householdIds->count(),
            'residents_active' => $householdIds->isEmpty()
                ? 0
                : Resident::whereIn('household_id', $householdIds)->domiciledActive()->count(),
            'residents_archived' => $householdIds->isEmpty()
                ? 0
                : Resident::whereIn('household_id', $householdIds)->domiciledArchived()->count(),
        ];
    }

    /** @return Builder<Resident> */
    public function buildResidentQuery(
        ?RtProfile $rt,
        string $filter,
        string $kategori,
        string $search,
        bool $withRtProfile = false,
    ): Builder {
        $with = [
            'latestNotificationLog',
            'household.pendataanDocuments',
            'household.headResident',
            'household.residents' => function ($q) {
                $q->with('latestNotificationLog')
                    ->orderByDesc('is_head_of_family')
                    ->orderBy('name');
            },
        ];
        if ($withRtProfile) {
            $with[] = 'household.rtProfile';
        }

        $query = Resident::query()
            ->select('residents.*')
            ->with($with)
            ->join('households', 'residents.household_id', '=', 'households.id');

        if ($rt) {
            $query->forRtProfile($rt);
        } else {
            $query->whereIn('households.rt_profile_id', $this->inaugaProfileIds());
        }

        $this->applyKategoriScopeOnJoinedHousehold($query, $kategori);

        if ($search !== '') {
            $term = '%'.$search.'%';
            $query->where(function ($outer) use ($term) {
                $outer->where(function ($householdMatch) use ($term) {
                    $householdMatch->where('households.family_card_number', 'like', $term)
                        ->orWhere('households.address', 'like', $term)
                        ->orWhere('households.suku', 'like', $term)
                        ->orWhere('households.house_number', 'like', $term);
                });

                $outer->orWhere('residents.name', 'like', $term)
                    ->orWhere('residents.nik', 'like', $term);
            });
        } else {
            $this->applyResidentListScope($query, $filter);
        }

        return $query
            ->orderBy('households.family_card_number')
            ->orderByDesc('residents.is_head_of_family')
            ->orderBy('residents.name');
    }

    /** @return Builder<Household> */
    public function buildHouseholdQuery(
        RtProfile $rt,
        string $filter,
        string $kategori,
        string $search,
    ): Builder {
        $query = Household::query()
            ->with([
                'rtProfile',
                'pendataanDocuments',
                'headResident',
                'residents' => function ($q) use ($filter, $search) {
                    if ($search === '') {
                        $this->applyResidentListScope($q, $filter);
                    } else {
                        $term = '%'.$search.'%';
                        $q->where(function ($inner) use ($term) {
                            $inner->where('name', 'like', $term)
                                ->orWhere('nik', 'like', $term)
                                ->orWhereHas('household', function ($h) use ($term) {
                                    $h->where('family_card_number', 'like', $term)
                                        ->orWhere('address', 'like', $term)
                                        ->orWhere('suku', 'like', $term)
                                        ->orWhere('house_number', 'like', $term);
                                });
                        });
                    }
                    $q->with('latestNotificationLog')
                        ->orderByDesc('is_head_of_family')
                        ->orderBy('name');
                },
            ])
            ->forRtProfile($rt)
            ->orderBy('family_card_number');

        if ($search !== '') {
            $term = '%'.$search.'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('family_card_number', 'like', $term)
                    ->orWhere('address', 'like', $term)
                    ->orWhere('suku', 'like', $term)
                    ->orWhere('house_number', 'like', $term)
                    ->orWhereHas('residents', function ($r) use ($term) {
                        $r->where(function ($inner) use ($term) {
                            $inner->where('name', 'like', $term)
                                ->orWhere('nik', 'like', $term);
                        });
                    });
            });
        } else {
            $query->whereHas('residents', fn ($r) => $this->applyResidentListScope($r, $filter));
        }

        $this->applyKategoriScope($query, $kategori);

        return $query;
    }

    public function pageForHousehold(
        RtProfile $rt,
        string $filter,
        string $kategori,
        string $search,
        int $householdId,
        int $perPage = self::HOUSEHOLDS_PER_PAGE,
    ): ?int {
        $ids = $this->buildHouseholdQuery($rt, $filter, $kategori, $search)
            ->pluck('id')
            ->all();

        $index = array_search($householdId, $ids, true);

        if ($index === false) {
            return null;
        }

        return (int) floor($index / $perPage) + 1;
    }

    public function pageForFocusedHousehold(
        RtProfile $rt,
        string $filter,
        string $kategori,
        string $search,
        int $householdId,
        int $perPage = self::RESIDENTS_PER_PAGE,
    ): ?int {
        $matchingIds = $this->buildResidentQuery($rt, $filter, $kategori, $search)
            ->where('residents.household_id', $householdId)
            ->pluck('residents.id')
            ->all();

        if ($matchingIds === []) {
            return null;
        }

        $allIds = $this->buildResidentQuery($rt, $filter, $kategori, $search)
            ->pluck('residents.id')
            ->all();

        $index = array_search($matchingIds[0], $allIds, true);

        if ($index === false) {
            return null;
        }

        return (int) floor($index / $perPage) + 1;
    }

    public function normalizeKategori(?string $kategori): string
    {
        $allowed = array_column(self::KATEGORI_OPTIONS, 'value');

        return in_array($kategori, $allowed, true) ? $kategori : 'semua';
    }

    /** @param  Builder<Resident>  $query */
    private function applyKategoriScopeOnJoinedHousehold(Builder $query, string $kategori): void
    {
        if ($kategori === 'semua') {
            return;
        }

        if ($kategori === 'entri_rt') {
            $query->where(function (Builder $sub) {
                $sub->where('households.pendataan_category', '')->orWhereNull('households.pendataan_category');
            });

            return;
        }

        $query->where('households.pendataan_category', $kategori);
    }

    /** @param  Builder<Household>  $query */
    private function applyKategoriScope(Builder $query, string $kategori): void
    {
        if ($kategori === 'semua') {
            return;
        }

        if ($kategori === 'entri_rt') {
            $query->where(function (Builder $sub) {
                $sub->where('pendataan_category', '')->orWhereNull('pendataan_category');
            });

            return;
        }

        $query->where('pendataan_category', $kategori);
    }

    /** @param  Builder<Resident>|Relation<Resident, Household, Resident>  $query */
    public function applyResidentListScope(Builder|Relation $query, string $filter): Builder|Relation
    {
        if ($filter === 'arsip') {
            return $query->domiciledArchived();
        }

        if ($filter === 'semua') {
            return $query;
        }

        return $query->domiciledActive();
    }

    /** @return list<int> */
    private function inaugaProfileIds(): array
    {
        return RtProfile::inauga()->pluck('id')->all();
    }
}
