<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Resident;
use App\Support\ApplicationTimeline;
use App\Support\PhoneNormalizer;
use App\Support\TrackContent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackController extends Controller
{
    public function form(): View
    {
        return view('track.form', $this->trackFormData());
    }

    public function show(Request $request): View
    {
        $mode = $request->input('mode', 'number');

        return match ($mode) {
            'nik' => $this->searchByNik($request),
            'whatsapp' => $this->searchByWhatsapp($request),
            default => $this->searchByNumber($request),
        };
    }

    protected function searchByNumber(Request $request): View
    {
        $validated = $request->validate([
            'application_number' => ['required', 'string'],
        ]);

        $application = Application::with(['serviceType', 'resident.household.rtProfile', 'assignedRtProfile'])
            ->where('application_number', $validated['application_number'])
            ->first();

        if (! $application) {
            return view('track.form', $this->trackFormData([
                'error' => 'Nomor permohonan tidak ditemukan.',
                'application_number' => $validated['application_number'],
                'mode' => 'number',
            ]));
        }

        return view('track.show', [
            'application' => $application,
            'timelineSteps' => ApplicationTimeline::stepsFor($application),
        ]);
    }

    protected function searchByNik(Request $request): View
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'size:16', 'regex:/^\d{16}$/'],
        ]);

        $resident = Resident::where('nik', $validated['nik'])->first();
        if (! $resident) {
            return view('track.form', $this->trackFormData([
                'error' => 'NIK tidak ditemukan dalam data warga.',
                'mode' => 'nik',
                'nik' => $validated['nik'],
            ]));
        }

        $applications = Application::with('serviceType')
            ->where('resident_id', $resident->id)
            ->where('submitted_at', '>=', now()->subDays(90))
            ->orderByDesc('submitted_at')
            ->limit(10)
            ->get();

        if ($applications->isEmpty()) {
            return view('track.form', $this->trackFormData([
                'error' => 'Tidak ada permohonan aktif dalam 90 hari terakhir untuk NIK ini.',
                'mode' => 'nik',
                'nik' => $validated['nik'],
            ]));
        }

        return view('track.list', [
            'applications' => $applications,
            'searchLabel' => 'NIK '.$validated['nik'],
        ]);
    }

    protected function searchByWhatsapp(Request $request): View
    {
        $validated = $request->validate([
            'phone' => PhoneNormalizer::validationRules(true),
        ]);

        $variants = PhoneNormalizer::variants($validated['phone']);
        $resident = Resident::query()
            ->where(function ($q) use ($variants) {
                foreach ($variants as $variant) {
                    $q->orWhere('phone', 'like', '%'.$variant.'%');
                }
            })
            ->first();

        if (! $resident) {
            return view('track.form', $this->trackFormData([
                'error' => 'Nomor WhatsApp tidak ditemukan dalam data warga.',
                'mode' => 'whatsapp',
                'phone' => $validated['phone'],
            ]));
        }

        $applications = Application::with('serviceType')
            ->where('resident_id', $resident->id)
            ->where('submitted_at', '>=', now()->subDays(90))
            ->orderByDesc('submitted_at')
            ->limit(10)
            ->get();

        if ($applications->isEmpty()) {
            return view('track.form', $this->trackFormData([
                'error' => 'Tidak ada permohonan aktif dalam 90 hari terakhir untuk nomor ini.',
                'mode' => 'whatsapp',
                'phone' => $validated['phone'],
            ]));
        }

        return view('track.list', [
            'applications' => $applications,
            'searchLabel' => 'WhatsApp '.$validated['phone'],
        ]);
    }

    /** @param  array<string, mixed>  $extra */
    protected function trackFormData(array $extra = []): array
    {
        return array_merge([
            'trackFaq' => config('kelurahan.track_faq', []),
            'formLead' => TrackContent::formLead(),
            'heroLead' => TrackContent::heroLead(),
        ], $extra);
    }
}
