<?php

namespace App\Http\Controllers\Kelurahan;

use App\Http\Controllers\Controller;
use App\Services\OperationalDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly OperationalDashboardService $operationalDashboard,
    ) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('kelurahan.dashboard', [
            'stats' => $this->operationalDashboard->stats(),
            'recentApplications' => $this->operationalDashboard->recentApplications(),
            'recentReports' => $this->operationalDashboard->recentReports(),
        ]);
    }
}
