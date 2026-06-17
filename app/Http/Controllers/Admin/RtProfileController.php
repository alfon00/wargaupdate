<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RtProfile;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RtProfileController extends Controller
{
    public function index(): View
    {
        $profiles = RtProfile::query()
            ->inauga()
            ->withCount([
                'users as staff_count' => function ($query) {
                    $query->whereIn('role', ['ketua_rt', 'sekretaris_rt'])
                        ->whereColumn('users.rt_profile_id', 'rt_profiles.id');
                },
                'users',
                'households',
                'publications',
                'citizenReports',
            ])
            ->when($q = trim((string) request('q', '')), function ($query) use ($q) {
                $term = '%'.$q.'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('rt_number', 'like', $term)
                        ->orWhere('rw_number', 'like', $term)
                        ->orWhere('ketua_rt', 'like', $term)
                        ->orWhere('kelurahan', 'like', $term);
                });
            })
            ->orderBy('rt_number')
            ->paginate(30)
            ->withQueryString();

        return view('admin.rt-profiles.index', compact('profiles'));
    }

    public function create(): View
    {
        return view('admin.rt-profiles.form', [
            'profile' => new RtProfile([
                'kelurahan' => config('kelurahan.nama'),
                'kecamatan' => config('kelurahan.distrik'),
                'kota' => config('kelurahan.kabupaten'),
                'provinsi' => config('kelurahan.provinsi'),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProfile($request);
        $validated['rt_number'] = RtProfile::normalizeRtNumber($validated['rt_number']);
        RtProfile::create($validated);

        return redirect()->route('admin.rt-profiles.index')->with('success', 'Profil RT berhasil ditambahkan.');
    }

    public function edit(RtProfile $rtProfile): View
    {
        return view('admin.rt-profiles.form', [
            'profile' => $rtProfile,
        ]);
    }

    public function update(Request $request, RtProfile $rtProfile): RedirectResponse
    {
        $validated = $this->validateProfile($request, $rtProfile);
        $validated['rt_number'] = RtProfile::normalizeRtNumber($validated['rt_number']);
        $rtProfile->update($validated);

        return redirect()->route('admin.rt-profiles.index')->with('success', 'Profil RT berhasil diperbarui.');
    }

    public function destroy(RtProfile $rtProfile): RedirectResponse
    {
        if ($reason = $rtProfile->deletionBlockReason()) {
            return redirect()->route('admin.rt-profiles.index')->withErrors(['delete' => $reason]);
        }

        $rtProfile->delete();

        return redirect()->route('admin.rt-profiles.index')->with('success', 'Profil RT berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validateProfile(Request $request, ?RtProfile $profile = null): array
    {
        return $request->validate([
            'rt_number' => ['required', 'string', 'max:20'],
            'rw_number' => ['nullable', 'string', 'max:20'],
            'kelurahan' => ['required', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kota' => ['nullable', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'ketua_rt' => ['nullable', 'string', 'max:255'],
            'sekretaris_rt' => ['nullable', 'string', 'max:255'],
            'ketua_rw' => ['nullable', 'string', 'max:255'],
            'alamat_kantor' => ['nullable', 'string', 'max:500'],
            'phone' => PhoneNormalizer::validationRules(),
            'whatsapp' => PhoneNormalizer::validationRules(),
            'email' => ['nullable', 'email', 'max:255'],
            'jam_layanan' => ['nullable', 'string', 'max:255'],
            'visi' => ['nullable', 'string'],
            'misi' => ['nullable', 'string'],
        ]);
    }
}
