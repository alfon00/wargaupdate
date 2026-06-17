<?php

namespace App\Http\Controllers\Kelurahan;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Services\ResidentDataIndexService;
use App\Services\ResidentFaceReferenceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResidentDataController extends Controller
{
    public function __construct(
        private readonly ResidentDataIndexService $residentDataIndex,
        private readonly ResidentFaceReferenceService $faceReferences,
    ) {}

    public function index(Request $request): View
    {
        $rtProfiles = RtProfile::forPublicSelect()->get();
        $selectedRt = $request->filled('rt_profile_id')
            ? $rtProfiles->firstWhere('id', (int) $request->input('rt_profile_id'))
            : null;

        $filter = $request->query('filter', 'aktif');
        $kategori = $this->residentDataIndex->normalizeKategori($request->query('kategori', 'semua'));
        $search = trim((string) $request->query('q', ''));

        $residents = $this->residentDataIndex
            ->buildResidentQuery($selectedRt, $filter, $kategori, $search, withRtProfile: true)
            ->paginate(20)
            ->withQueryString();

        return view('kelurahan.resident-data.index', [
            'residents' => $residents,
            'rtProfiles' => $rtProfiles,
            'selectedRt' => $selectedRt,
            'filter' => $filter,
            'kategori' => $kategori,
            'kategoriOptions' => ResidentDataIndexService::KATEGORI_OPTIONS,
            'stats' => $this->residentDataIndex->stats($selectedRt),
            'focusHouseholdId' => $request->query('household'),
        ]);
    }

    public function show(Request $request, Resident $resident): View
    {
        $this->abortUnlessInaugaResident($resident);

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
            'rt_profile_id' => $request->query('rt_profile_id'),
            'household' => $request->query('household', $resident->household_id),
        ], fn ($value) => $value !== null && $value !== '');

        $faceReadiness = $resident->household
            ? $this->faceReferences->readinessForHousehold($resident->household)
            : null;

        return view('kelurahan.resident-data.show', [
            'resident' => $resident,
            'listQuery' => $listQuery,
            'faceReadiness' => $faceReadiness,
        ]);
    }

    private function abortUnlessInaugaResident(Resident $resident): void
    {
        $resident->loadMissing('household.rtProfile');

        $profileId = $resident->household?->rt_profile_id;
        if (! $profileId || ! RtProfile::inauga()->whereKey($profileId)->exists()) {
            abort(404);
        }
    }
}
