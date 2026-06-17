<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\GuestResidentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendataanWargaController extends Controller
{
    public function __construct(
        protected GuestResidentService $guestResidents,
    ) {}

    public function create(): View
    {
        $oldMembers = array_values(old('members', []));
        if ($oldMembers === []) {
            $oldMembers = [[]];
        }

        return view('public.services.pendataan-warga', [
            'rtProfiles' => GuestResidentService::rtProfilesForSelect(),
            'demographics' => config('kelurahan.resident_demographics', []),
            'maxMembers' => (int) config('kelurahan.pendataan_max_anggota', 50),
            'oldMembers' => $oldMembers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $result = $this->guestResidents->submitPendataanWargaBaru($request);

        return redirect()
            ->route('services.pendataan-warga.success')
            ->with('pendataan_warga_success', [
                'name' => $result['head']->name,
                'rt_label' => $result['household']->rtProfile?->displayName() ?? 'RT',
            ]);
    }

    public function success(): View|RedirectResponse
    {
        $data = session('pendataan_warga_success');
        if (! is_array($data)) {
            return redirect()
                ->route('services.pendataan-warga')
                ->with('info', 'Halaman konfirmasi tidak tersedia. Jika baru saja mengirim formulir, cek notifikasi WhatsApp atau hubungi pengurus RT.');
        }

        return view('public.services.pendataan-warga-success', ['data' => $data]);
    }
}
