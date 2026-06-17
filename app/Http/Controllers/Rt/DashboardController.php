<?php

namespace App\Http\Controllers\Rt;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Rt\Concerns\ResolvesRtProfile;
use App\Models\Application;
use App\Models\CitizenReport;
use App\Models\Resident;
use App\Support\RtPopulationAnalytics;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesRtProfile;

    public function __invoke(): View
    {
        $rt = $this->resolvedRtProfile();

        if (! $rt) {
            return view('rt.dashboard', [
                'rt' => null,
                'linkingHint' => $this->linkingHintForUser(),
                'priorities' => collect(),
                'activities' => collect(),
            ]);
        }

        $applicationsQuery = Application::forRtProfile($rt);
        $residentsQuery = Resident::forRtProfile($rt);
        $reportsQuery = CitizenReport::forRtProfile($rt);

        $pendingApplicationsCount = (clone $applicationsQuery)
            ->pendingRtSidebar()
            ->count();

        $pendingPendataanCount = (clone $residentsQuery)
            ->where('is_head_of_family', true)
            ->pendingPendataan()
            ->count();

        $priorities = collect();
        $pendingApplications = (clone $applicationsQuery)
            ->pendingRtSidebar()
            ->latest('submitted_at')
            ->limit(3)
            ->get(['id', 'application_number', 'status', 'submitted_at']);
        foreach ($pendingApplications as $application) {
            $priorities->push([
                'label' => 'Permohonan '.$application->application_number.' menunggu proses',
                'meta' => 'Diajukan '.optional($application->submitted_at)->timezone('Asia/Jayapura')->format('d/m/Y H:i'),
                'url' => route('rt.applications.show', $application),
                'tone' => 'warn',
            ]);
        }

        $pendingPendataan = (clone $residentsQuery)
            ->where('is_head_of_family', true)
            ->pendingPendataan()
            ->latest('updated_at')
            ->limit(3)
            ->get(['id', 'name', 'updated_at']);
        foreach ($pendingPendataan as $resident) {
            $priorities->push([
                'label' => 'Pendataan '.$resident->name.' perlu verifikasi',
                'meta' => 'Diperbarui '.optional($resident->updated_at)->timezone('Asia/Jayapura')->format('d/m/Y H:i'),
                'url' => route('rt.pendataan.show', $resident),
                'tone' => 'danger',
            ]);
        }

        $newReports = (clone $reportsQuery)
            ->where('status', ReportStatus::Baru)
            ->latest('created_at')
            ->limit(3)
            ->get(['id', 'report_number', 'subject', 'created_at']);
        foreach ($newReports as $report) {
            $priorities->push([
                'label' => 'Laporan '.$report->report_number.' baru diterima',
                'meta' => 'Topik: '.($report->subject ?: 'Laporan warga'),
                'url' => route('rt.reports.show', $report),
                'tone' => 'info',
            ]);
        }

        $activities = collect();
        foreach ($pendingApplications as $application) {
            $activities->push([
                'type' => 'permohonan',
                'title' => 'Permohonan '.$application->application_number,
                'timestamp' => $application->submitted_at,
                'url' => route('rt.applications.show', $application),
            ]);
        }
        foreach ($pendingPendataan as $resident) {
            $activities->push([
                'type' => 'pendataan',
                'title' => 'Pendataan '.$resident->name,
                'timestamp' => $resident->updated_at,
                'url' => route('rt.pendataan.show', $resident),
            ]);
        }
        foreach ($newReports as $report) {
            $activities->push([
                'type' => 'laporan',
                'title' => 'Laporan '.$report->report_number,
                'timestamp' => $report->created_at,
                'url' => route('rt.reports.show', $report),
            ]);
        }
        $activities = $activities
            ->sortByDesc(fn (array $item) => optional($item['timestamp'])->getTimestamp() ?? 0)
            ->take(8)
            ->values();

        $analytics = RtPopulationAnalytics::forRtProfile($rt);

        return view('rt.dashboard', [
            'rt' => $rt,
            'linkingHint' => null,
            'priorities' => $priorities->take(6)->values(),
            'activities' => $activities,
            'stats' => [
                'residents_active' => (int) ($analytics['population']['total'] ?? 0),
                'households' => (int) ($analytics['population']['households'] ?? 0),
                'pending_pendataan' => $pendingPendataanCount,
                'pending_applications' => $pendingApplicationsCount,
            ],
        ]);
    }

    private function linkingHintForUser(): ?string
    {
        $user = auth()->user();
        if (! $user?->isRtStaff()) {
            return null;
        }

        if ($user->rt_profile_id) {
            return 'Akun Anda memiliki profil RT (#'.$user->rt_profile_id.') tetapi tidak dapat di-resolve. Hubungi admin untuk memperbaiki penautan.';
        }

        $roleLabel = $user->role === \App\Enums\UserRole::KetuaRt ? 'Ketua RT' : 'Sekretaris RT';

        return 'Sebagai '.$roleLabel.', akun Anda belum ditautkan ke profil RT di Admin → Pengguna. Data layanan dari /layanan tidak akan tampil sampai rt_profile_id diisi.';
    }
}
