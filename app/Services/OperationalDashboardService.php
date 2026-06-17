<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Models\Application;
use App\Models\CitizenReport;
use App\Models\Household;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Collection;

class OperationalDashboardService
{
    /** @return array{residents: int, households: int, applications: int, pending: int, new_reports: int} */
    public function stats(): array
    {
        return [
            'residents' => Resident::count(),
            'households' => Household::count(),
            'applications' => Application::count(),
            'pending' => Application::pendingRtSidebar()->count(),
            'new_reports' => CitizenReport::where('status', ReportStatus::Baru)->count(),
        ];
    }

    /** @return Collection<int, Application> */
    public function recentApplications(int $limit = 10): Collection
    {
        return Application::with(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, CitizenReport> */
    public function recentReports(int $limit = 10): Collection
    {
        return CitizenReport::latest()
            ->limit($limit)
            ->get();
    }
}
