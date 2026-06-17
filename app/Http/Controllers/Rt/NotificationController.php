<?php

namespace App\Http\Controllers\Rt;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rt\Concerns\ResolvesRtProfile;
use App\Models\NotificationLog;
use Illuminate\View\View;

class NotificationController extends Controller
{
    use ResolvesRtProfile;

    public function index(): View
    {
        $rt = $this->requireRtProfile();

        $logs = NotificationLog::with(['application', 'resident', 'citizenReport', 'rtPublication'])
            ->forRtProfile($rt)
            ->latest()
            ->paginate(30);

        return view('rt.notifications.index', compact('logs', 'rt'));
    }
}
