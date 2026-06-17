<?php

namespace App\Http\Controllers\Kelurahan;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\CitizenReport;
use App\Models\RtProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactReportController extends Controller
{
    public function index(Request $request): View
    {
        $rtProfiles = RtProfile::forPublicSelect()->get();
        $categories = config('kelurahan.laporan_kategori', []);

        $query = CitizenReport::with('rtProfile')->latest();

        if ($request->filled('rt_profile_id')) {
            $query->where('rt_profile_id', (int) $request->rt_profile_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('q')) {
            $term = '%'.$request->q.'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('report_number', 'like', $term)
                    ->orWhere('reporter_name', 'like', $term);
            });
        }

        $reports = $query->paginate(20)->withQueryString();

        return view('kelurahan.reports.index', compact('reports', 'rtProfiles', 'categories'));
    }

    public function show(CitizenReport $report): View
    {
        $report->load(['rtProfile', 'handler']);

        return view('kelurahan.reports.show', compact('report'));
    }

    public function updateStatus(Request $request, CitizenReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                ReportStatus::Ditindak->value,
                ReportStatus::Selesai->value,
            ])],
            'response_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $report->update([
            'status' => $validated['status'],
            'response_note' => $validated['response_note'] ?? $report->response_note,
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        return back()->with('success', 'Status laporan diperbarui.');
    }
}
