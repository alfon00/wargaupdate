<?php

namespace App\Http\Controllers\Public;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendReportWhatsApp;
use App\Models\CitizenReport;
use App\Models\RtProfile;
use App\Services\GuestResidentService;
use App\Support\ContactContent;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactReportController extends Controller
{
    public function __construct(
        protected GuestResidentService $guestResidents
    ) {}

    public function create(): View
    {
        return view('public.contact.create', [
            'rtProfiles' => GuestResidentService::rtProfilesForSelect(),
            'categories' => config('kelurahan.laporan_kategori', []),
            'incidentTypes' => config('kelurahan.pengaduan_jenis', []),
            'introTitle' => ContactContent::introTitle(),
            'introLead' => ContactContent::introLead(),
            'formLead' => ContactContent::formLead(),
            'benefits' => ContactContent::benefits(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $categories = array_keys(config('kelurahan.laporan_kategori', []));
        $isEnvironment = $request->input('category') === 'pengaduan_lingkungan';

        $rules = [
            'rt_profile_id' => ['required', 'integer', 'exists:rt_profiles,id'],
            'category' => ['required', 'string', Rule::in($categories)],
            'reporter_name' => ['required', 'string', 'max:120'],
            'phone' => PhoneNormalizer::validationRules(true),
            'nik' => ['nullable', 'string', 'size:16', 'regex:/^\d{16}$/'],
            'email' => ['nullable', 'email', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
            'declaration' => ['accepted'],
        ];

        if ($isEnvironment) {
            $rules['incident_type'] = ['required', 'string', Rule::in(array_keys(config('kelurahan.pengaduan_jenis', [])))];
            $rules['incident_location'] = ['required', 'string', 'max:200'];
            $rules['photo'] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'];
        }

        $validated = $request->validate($rules, [
            'declaration.accepted' => 'Anda harus menyatakan bahwa laporan ini benar dan bukan spam.',
        ]);

        $canonicalId = $this->guestResidents->ensureRtInauga((int) $validated['rt_profile_id']);
        $rt = RtProfile::findOrFail($canonicalId);

        $categoryLabel = config('kelurahan.laporan_kategori.'.$validated['category'], 'Laporan');
        $subject = Str::limit($categoryLabel.' — '.$validated['message'], 120, '…');

        $report = CitizenReport::create([
            'report_number' => CitizenReport::generateNumber($rt->rt_number),
            'rt_profile_id' => $canonicalId,
            'category' => $validated['category'],
            'reporter_name' => $validated['reporter_name'],
            'phone' => $validated['phone'],
            'nik' => $validated['nik'] ?? null,
            'email' => $validated['email'] ?? null,
            'subject' => $subject,
            'message' => $validated['message'],
            'incident_type' => $isEnvironment ? ($validated['incident_type'] ?? null) : null,
            'incident_location' => $isEnvironment ? ($validated['incident_location'] ?? null) : null,
            'photo_path' => $isEnvironment && $request->hasFile('photo')
                ? $request->file('photo')->store('pengaduan-photos', 'public')
                : null,
            'status' => ReportStatus::Baru,
            'ip_address' => $request->ip(),
        ]);

        SendReportWhatsApp::dispatch($report->id, 'report_submitted');

        return redirect()
            ->route('contact.success')
            ->with('contact_success', [
                'report_number' => $report->report_number,
                'rt_label' => $rt->displayName(),
            ]);
    }

    public function success(): View|RedirectResponse
    {
        $data = session('contact_success');
        if (! is_array($data)) {
            return redirect()
                ->route('contact.create')
                ->with('info', 'Halaman konfirmasi tidak tersedia. Jika baru saja mengirim formulir, cek notifikasi WhatsApp atau hubungi pengurus RT.');
        }

        return view('public.contact.success', ['data' => $data]);
    }
}
