<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Services\GuestResidentService;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LetterServiceController extends Controller
{
    public function __construct(
        protected GuestResidentService $guestResidents,
    ) {}

    public function create(): View
    {
        return view('public.services.surat-index', [
            'services' => ServiceType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function verifyForm(Request $request): View|RedirectResponse
    {
        $code = $request->session()->get('surat_intended_service_code');
        if (! $code) {
            return redirect()
                ->route('services.surat')
                ->with('info', 'Pilih jenis surat terlebih dahulu, lalu klik Ajukan.');
        }

        $service = ServiceType::where('code', $code)->where('is_active', true)->first();
        if (! $service) {
            $request->session()->forget('surat_intended_service_code');

            return redirect()
                ->route('services.surat')
                ->with('info', 'Jenis surat tidak ditemukan. Silakan pilih ulang.');
        }

        return view('public.services.surat-verify', [
            'service' => $service,
            'rtProfiles' => GuestResidentService::rtProfilesForSelect(),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rt_profile_id' => ['required', 'exists:rt_profiles,id'],
            'nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/'],
            'phone' => PhoneNormalizer::validationRules(true),
        ]);

        $code = $request->session()->get('surat_intended_service_code');
        $service = $code
            ? ServiceType::where('code', $code)->where('is_active', true)->first()
            : null;

        if (! $service) {
            return redirect()
                ->route('services.surat')
                ->with('info', 'Pilih jenis surat terlebih dahulu, lalu klik Ajukan.');
        }

        $resident = $this->guestResidents->verifyForLetterService($validated);

        $request->session()->put('surat_resident_id', $resident->id);

        return redirect()
            ->route('services.apply', $service);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->guestResidents->endSuratSession($request);

        return redirect()
            ->route('services.surat')
            ->with('info', 'Sesi identitas diakhiri. Anda dapat memilih jenis surat lain.');
    }
}
