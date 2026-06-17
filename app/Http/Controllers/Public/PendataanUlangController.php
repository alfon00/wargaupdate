<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Services\GuestResidentService;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendataanUlangController extends Controller
{
    public function __construct(
        protected GuestResidentService $guestResidents
    ) {}

    public function create(Request $request): View
    {
        $resident = null;
        $members = [];

        if ($request->session()->has('pendataan_ulang_resident_id')) {
            $resident = Resident::with(['household.rtProfile', 'household.residents'])
                ->find($request->session()->get('pendataan_ulang_resident_id'));

            if ($resident?->household) {
                $members = $this->guestResidents->membersForPendataanUlangForm($resident->household);
            }
        }

        return view('public.services.pendataan-ulang', [
            'rtProfiles' => GuestResidentService::rtProfilesForSelect(),
            'resident' => $resident,
            'members' => $members,
            'demographics' => config('kelurahan.resident_demographics', []),
            'maxMembers' => (int) config('kelurahan.pendataan_max_anggota', 50),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rt_profile_id' => ['required', 'exists:rt_profiles,id'],
            'nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/'],
            'phone' => PhoneNormalizer::validationRules(true),
        ]);

        $resident = $this->guestResidents->findByNik($validated['nik']);
        if (! $resident) {
            return back()->withInput()->withErrors([
                'nik' => 'NIK tidak ditemukan. Hubungi pengurus RT untuk pencatatan data keluarga.',
            ]);
        }

        if ($resident->domicile_status?->isArchived()) {
            return back()->withInput()->withErrors([
                'nik' => 'Data Anda berstatus arsip. Hubungi pengurus RT jika perlu koreksi.',
            ]);
        }

        $phoneVariants = PhoneNormalizer::variants($validated['phone']);
        $registeredPhone = $resident->registeredPhoneForVerification();
        $residentPhone = PhoneNormalizer::digits($registeredPhone);
        if (! in_array($residentPhone, array_map([PhoneNormalizer::class, 'digits'], $phoneVariants), true)
            && ! in_array($registeredPhone, PhoneNormalizer::variants($validated['phone']), true)) {
            return back()->withInput()->withErrors([
                'phone' => 'Nomor HP tidak cocok dengan data terdaftar.',
            ]);
        }

        $rt = RtProfile::findOrFail(
            $this->guestResidents->ensureRtInauga((int) $validated['rt_profile_id'])
        );
        $household = $resident->household;
        $profileIds = RtProfile::profileIdsForRtNumber($rt->rt_number);
        if (! $household || ! in_array((int) $household->rt_profile_id, $profileIds, true)) {
            return back()->withInput()->withErrors([
                'rt_profile_id' => 'NIK terdaftar di RT lain. Pilih RT yang sesuai.',
            ]);
        }

        $request->session()->put('pendataan_ulang_resident_id', $resident->id);

        return redirect()->route('services.pendataan-ulang');
    }

    public function store(Request $request): RedirectResponse
    {
        $residentId = $request->session()->get('pendataan_ulang_resident_id');
        abort_unless($residentId, 403);

        $resident = Resident::with(['household.rtProfile', 'household.residents'])->findOrFail($residentId);
        $household = $resident->household;
        abort_unless($household, 404);

        $result = $this->guestResidents->submitPendataanUlangDocuments($request, $household);

        $request->session()->forget('pendataan_ulang_resident_id');

        return redirect()
            ->route('services.pendataan-ulang.success')
            ->with('pendataan_ulang_success', [
                'name' => $result['head']->name,
                'rt_label' => $household->rtProfile?->displayName() ?? 'RT',
            ]);
    }

    public function success(): View|RedirectResponse
    {
        $data = session('pendataan_ulang_success');
        if (! is_array($data)) {
            return redirect()
                ->route('services.pendataan-ulang')
                ->with('info', 'Halaman konfirmasi tidak tersedia. Jika baru saja mengirim formulir, cek notifikasi WhatsApp atau hubungi pengurus RT.');
        }

        return view('public.services.pendataan-ulang-success', ['data' => $data]);
    }
}
