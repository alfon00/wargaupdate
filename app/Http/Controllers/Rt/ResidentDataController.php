<?php

namespace App\Http\Controllers\Rt;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rt\Concerns\ResolvesRtProfile;
use App\Jobs\SendPendataanWhatsApp;
use App\Models\RtProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ResidentDataIndexService;
use App\Services\RtHouseholdRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResidentDataController extends Controller
{
    use ResolvesRtProfile;

    public function __construct(
        private readonly RtHouseholdRegistrationService $registrationService,
        private readonly ResidentDataIndexService $residentDataIndex,
    ) {}

    public function create(): View
    {
        $rt = $this->requireRtProfile();

        return view('rt.resident-data.create', [
            'rt' => $rt,
            'maxMembers' => (int) config('kelurahan.pendataan_max_anggota', 50),
            'demographics' => config('kelurahan.resident_demographics', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rt = $this->requireRtProfile();
        $result = $this->registrationService->register($request, $rt);

        $head = $result['members']->firstWhere('is_head_of_family', true)
            ?? $result['members']->first();
        if ($head) {
            SendPendataanWhatsApp::dispatchSync($head->id, 'pendataan_verified');
        }

        $redirect = redirect()
            ->route('rt.data-warga.index', ['household' => $result['household']->id, 'filter' => 'aktif'])
            ->with('success', 'KK dan '.($result['members']->count()).' anggota berhasil didaftarkan.');

        if (! empty($result['face_sync_warning'])) {
            $redirect->with('face_sync_warning', $result['face_sync_warning']);
        }

        return $redirect;
    }

    public function index(Request $request): View|RedirectResponse
    {
        $rt = $this->requireRtProfile();
        $filter = $request->query('filter', 'aktif');
        $kategori = $this->residentDataIndex->normalizeKategori($request->query('kategori', 'semua'));
        $search = trim((string) $request->query('q', ''));
        $perPage = ResidentDataIndexService::RESIDENTS_PER_PAGE;
        $focusHouseholdId = $request->query('household');

        if (filled($focusHouseholdId)) {
            $expectedPage = $this->residentDataIndex->pageForFocusedHousehold(
                $rt,
                $filter,
                $kategori,
                $search,
                (int) $focusHouseholdId,
                $perPage,
            );
            $currentPage = max(1, (int) $request->query('page', 1));

            if ($expectedPage !== null && $expectedPage !== $currentPage) {
                return redirect()->route('rt.data-warga.index', array_merge(
                    $request->query(),
                    ['page' => $expectedPage],
                ));
            }
        }

        $residents = $this->residentDataIndex
            ->buildResidentQuery($rt, $filter, $kategori, $search)
            ->paginate($perPage)
            ->withQueryString();

        return view('rt.resident-data.index', [
            'residents' => $residents,
            'rt' => $rt,
            'filter' => $filter,
            'kategori' => $kategori,
            'kategoriOptions' => ResidentDataIndexService::KATEGORI_OPTIONS,
            'stats' => $this->residentDataIndex->stats($rt),
            'focusHouseholdId' => $focusHouseholdId,
        ]);
    }

    public function report(Request $request)
    {
        $rt = $this->requireRtProfile();
        $filter = $request->query('filter', 'aktif');
        $kategori = $this->residentDataIndex->normalizeKategori($request->query('kategori', 'semua'));
        $search = trim((string) $request->query('q', ''));

        $households = $this->residentDataIndex
            ->buildHouseholdQuery($rt, $filter, $kategori, $search)
            ->get();

        $pdf = Pdf::loadView('rt.resident-data.report-pdf', [
            'rt' => $rt,
            'households' => $households,
            'filter' => $filter,
            'kategori' => $kategori,
            'search' => $search,
            'generatedAt' => now('Asia/Jayapura'),
        ])->setPaper('A4');

        return $pdf->download('laporan-rt-data-warga-'.now('Asia/Jayapura')->format('Ymd-His').'.pdf');
    }
}
