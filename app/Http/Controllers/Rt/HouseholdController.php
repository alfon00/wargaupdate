<?php

namespace App\Http\Controllers\Rt;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rt\Concerns\ResolvesRtProfile;
use App\Http\Controllers\Rt\Concerns\RedirectsAfterPendataanEdit;
use App\Models\Household;
use App\Services\PermanentDeletionRequestService;
use App\Services\ResidentFaceReferenceService;
use App\Support\ResidentLetterProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HouseholdController extends Controller
{
    use ResolvesRtProfile;
    use RedirectsAfterPendataanEdit;

    public function __construct(
        private readonly PermanentDeletionRequestService $deletionRequestService,
        private readonly ResidentFaceReferenceService $faceReferences,
    ) {}

    public function create(): RedirectResponse
    {
        $this->requireRtProfile();

        return redirect()->route('rt.data-warga.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireRtProfile();

        return redirect()
            ->route('rt.data-warga.create')
            ->with('success', 'Gunakan formulir gabungan untuk mendaftarkan KK dan anggota sekaligus.');
    }

    public function edit(Household $household): View
    {
        $this->abortUnlessOwnsHousehold($household);
        $rt = $this->requireRtProfile();

        $household->load(['headResident', 'rtProfile']);

        return view('rt.households.form', [
            'household' => $household,
            'rt' => $rt,
            'pendataanReturn' => request('return') === 'pendataan' ? request('pendataan_head') : null,
        ]);
    }

    public function update(Request $request, Household $household): RedirectResponse
    {
        $this->abortUnlessOwnsHousehold($household);
        $rt = $this->requireRtProfile();
        $household->update($this->validateHousehold($request, $rt->id, $household));

        $request->merge(['household_id' => $household->id]);

        return $this->redirectAfterPendataanRelatedUpdate($request, 'Data KK berhasil diperbarui.');
    }

    public function syncFaceReferences(Household $household): RedirectResponse
    {
        $this->abortUnlessOwnsHousehold($household);

        $summary = $this->faceReferences->syncForHouseholdWithSummary($household);

        $redirect = redirect()
            ->route('rt.data-warga.index', ['household' => $household->id])
            ->with(
                $summary['ok'] ? 'success' : 'face_sync_warning',
                $summary['message'],
            );

        return $redirect;
    }

    public function destroy(Request $request, Household $household): RedirectResponse
    {
        $this->abortUnlessOwnsHousehold($household);

        $request->validate(
            ResidentLetterProfile::rtChairSignatureRules(),
            ResidentLetterProfile::rtChairSignatureMessages(),
        );

        try {
            $this->deletionRequestService->submitHousehold($request, $household, $request->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('rt.data-warga.index', ['household' => $household->id])
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('rt.data-warga.index', array_filter([
                'filter' => request('filter', 'aktif'),
                'kategori' => request('kategori'),
                'household' => $household->id,
            ]))
            ->with('success', 'Pengajuan hapus permanen KK dikirim ke admin kelurahan. Data belum dihapus sampai disetujui.');
    }

    /** @return array<string, mixed> */
    protected function validateHousehold(Request $request, int $rtProfileId, ?Household $household = null): array
    {
        if ($request->has('family_card_number')) {
            $request->merge([
                'family_card_number' => ResidentLetterProfile::normalizeFamilyCardNumber(
                    $request->input('family_card_number')
                ),
            ]);
        }

        $rules = array_merge(
            ResidentLetterProfile::householdFormValidationRules(),
            ['status' => ['nullable', 'string', 'max:20']],
        );

        if ($household) {
            $rules['suku'] = ['nullable', 'string', 'max:100'];
        }

        $validated = $request->validate($rules, ResidentLetterProfile::householdFormValidationMessages());

        $validated['rt_profile_id'] = $rtProfileId;

        return $validated;
    }
}
