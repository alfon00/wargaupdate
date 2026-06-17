<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OperationalDashboardService;
use App\Support\RtPopulationAnalytics;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly OperationalDashboardService $operationalDashboard,
    ) {}

    public function __invoke(): View
    {
        $analytics = RtPopulationAnalytics::forKelurahan();

        return view('admin.dashboard', [
            'stats' => $this->operationalDashboard->stats(),
            'recentApplications' => $this->operationalDashboard->recentApplications(),
            'recentReports' => $this->operationalDashboard->recentReports(),
            'analytics' => $analytics,
            'monograph' => RtPopulationAnalytics::monographTable(null),
            'populationResidentsActive' => (int) ($analytics['population']['total'] ?? 0),
        ]);
    }
}
