<?php

namespace App\Http\Controllers\Rt;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Rt\Concerns\ResolvesRtProfile;
use App\Jobs\SendReportWhatsApp;
use App\Models\CitizenReport;
use App\Models\NotificationLog;
use App\Services\CitizenReportDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactReportController extends Controller
{
    use ResolvesRtProfile;

    public function __construct(
        protected CitizenReportDeletionService $reportDeletion,
    ) {}

    public function index(): View
    {
        $rt = $this->requireRtProfile();

        $query = CitizenReport::with('rtProfile')
            ->forRtProfile($rt)
            ->latest();

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        if ($q = trim((string) request('q', ''))) {
            $term = '%'.$q.'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('report_number', 'like', $term)
                    ->orWhere('reporter_name', 'like', $term)
                    ->orWhere('subject', 'like', $term);
            });
        }

        $reports = $query->paginate(20)->withQueryString();

        return view('rt.reports.index', compact('reports', 'rt'));
    }

    public function destroy(CitizenReport $report): RedirectResponse
    {
        $this->abortUnlessOwnsReport($report);

        $number = $report->report_number;

        $this->reportDeletion->delete($report);

        return redirect()
            ->route('rt.reports.index', request()->only(['q', 'status']))
            ->with('success', "Laporan {$number} berhasil dihapus.");
    }

    public function show(CitizenReport $report): View
    {
        $this->abortUnlessOwnsReport($report);
        $report->load(['rtProfile', 'handler']);

        $notificationLogs = NotificationLog::query()
            ->forCitizenReport($report->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('rt.reports.show', compact('report', 'notificationLogs'));
    }

    public function updateStatus(Request $request, CitizenReport $report): RedirectResponse
    {
        $this->abortUnlessOwnsReport($report);

        $validated = $request->validate([
            'status' => ['required', Rule::in([ReportStatus::Ditindak->value, ReportStatus::Selesai->value])],
            'response_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $previousStatus = $report->status;

        $report->update([
            'status' => $validated['status'],
            'response_note' => $validated['response_note'] ?? $report->response_note,
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        if ($previousStatus !== $report->status) {
            SendReportWhatsApp::dispatch($report->id, 'report_status_updated');
        }

        return back()->with('success', 'Status laporan diperbarui.');
    }
}
