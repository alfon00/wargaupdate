<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PermanentDeletionRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\PermanentDeletionRequest;
use App\Services\PermanentDeletionRequestService;
use App\Support\SignatureStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermanentDeletionRequestController extends Controller
{
    public function __construct(
        private readonly PermanentDeletionRequestService $deletionRequests,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', PermanentDeletionRequestStatus::Pending->value);

        if ($status === 'all') {
            $statusEnum = null;
        } else {
            $statusEnum = PermanentDeletionRequestStatus::tryFrom($status)
                ?? PermanentDeletionRequestStatus::Pending;
            $status = $statusEnum->value;
        }

        $requests = PermanentDeletionRequest::query()
            ->with(['rtProfile', 'requester'])
            ->when($statusEnum, fn ($query) => $query->where('status', $statusEnum))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.deletion-requests.index', [
            'requests' => $requests,
            'status' => $status,
            'statuses' => PermanentDeletionRequestStatus::cases(),
            'pendingCount' => $this->deletionRequests->pendingCount(),
        ]);
    }

    public function show(PermanentDeletionRequest $deletionRequest): View
    {
        $deletionRequest->load(['rtProfile', 'requester', 'reviewer', 'resident', 'household']);

        return view('admin.deletion-requests.show', [
            'deletionRequest' => $deletionRequest,
            'signatureDataUri' => SignatureStorage::toDataUriFromPath($deletionRequest->signature_path),
        ]);
    }

    public function approve(PermanentDeletionRequest $deletionRequest): RedirectResponse
    {
        try {
            $this->deletionRequests->approve($deletionRequest, request()->user());
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()
                ->route('admin.deletion-requests.show', $deletionRequest)
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.deletion-requests.index', ['status' => PermanentDeletionRequestStatus::Pending->value])
            ->with('success', 'Permintaan hapus permanen disetujui. Data target telah dihapus.');
    }

    public function reject(Request $request, PermanentDeletionRequest $deletionRequest): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->deletionRequests->reject(
                $deletionRequest,
                $request->user(),
                $validated['admin_notes'] ?? null,
            );
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return redirect()
                ->route('admin.deletion-requests.show', $deletionRequest)
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.deletion-requests.show', $deletionRequest)
            ->with('success', 'Permintaan hapus permanen ditolak.');
    }
}
