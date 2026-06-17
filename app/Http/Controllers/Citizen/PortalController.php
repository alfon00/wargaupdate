<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __invoke(): View
    {
        $resident = auth()->user()->resident;

        $applications = $resident
            ? Application::where('resident_id', $resident->id)->latest()->limit(5)->get()
            : collect();

        return view('citizen.portal', [
            'resident' => $resident,
            'applications' => $applications,
            'stats' => [
                'total' => $resident ? Application::where('resident_id', $resident->id)->count() : 0,
                'pending' => $resident ? Application::where('resident_id', $resident->id)->whereNotIn('status', ['disetujui', 'ditolak', 'siap_diambil'])->count() : 0,
            ],
        ]);
    }
}
