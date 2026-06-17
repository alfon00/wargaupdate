<?php

namespace App\Services;

use App\Enums\PermanentDeletionRequestStatus;
use App\Models\Household;
use App\Models\PermanentDeletionRequest;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\User;
use App\Support\SignatureStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PermanentDeletionRequestService
{
    public function __construct(
        private readonly RtResidentDeletionService $deletionService,
    ) {}

    public function submitResident(Request $request, Resident $resident, User $requester): PermanentDeletionRequest
    {
        $resident->loadMissing('household.rtProfile');

        $check = $this->deletionService->canDeleteResident($resident);
        if (! $check['allowed']) {
            throw ValidationException::withMessages([
                'delete' => $check['reason'] ?? 'Data warga tidak dapat dihapus.',
            ]);
        }

        $this->assertNoPendingRequest('resident', $resident->id, null);

        $signatureData = (string) $request->input('signature_data');
        $rtProfile = $resident->household?->rtProfile;

        return DB::transaction(function () use ($requester, $resident, $signatureData, $rtProfile) {
            $requestNumber = $this->generateRequestNumber($rtProfile);

            return PermanentDeletionRequest::create([
                'request_number' => $requestNumber,
                'rt_profile_id' => $rtProfile?->id ?? $requester->rt_profile_id,
                'requested_by' => $requester->id,
                'target_type' => 'resident',
                'resident_id' => $resident->id,
                'household_id' => $resident->household_id,
                'target_name' => $resident->name,
                'target_nik' => $resident->nik,
                'family_card_number' => $resident->household?->family_card_number,
                'signature_path' => SignatureStorage::storeDeletionRequest($signatureData, 'resident-'.$resident->id),
                'status' => PermanentDeletionRequestStatus::Pending,
            ]);
        });
    }

    public function submitHousehold(Request $request, Household $household, User $requester): PermanentDeletionRequest
    {
        $household->loadMissing('rtProfile');

        $check = $this->deletionService->canDeleteHousehold($household);
        if (! $check['allowed']) {
            throw ValidationException::withMessages([
                'delete' => $check['reason'] ?? 'Kartu keluarga tidak dapat dihapus.',
            ]);
        }

        $this->assertNoPendingRequest('household', null, $household->id);

        $signatureData = (string) $request->input('signature_data');
        $head = $household->headOfFamily();

        return DB::transaction(function () use ($requester, $household, $signatureData, $head) {
            $requestNumber = $this->generateRequestNumber($household->rtProfile);

            return PermanentDeletionRequest::create([
                'request_number' => $requestNumber,
                'rt_profile_id' => $household->rt_profile_id,
                'requested_by' => $requester->id,
                'target_type' => 'household',
                'resident_id' => null,
                'household_id' => $household->id,
                'target_name' => $head?->name ?? ('KK '.$household->family_card_number),
                'target_nik' => $head?->nik,
                'family_card_number' => $household->family_card_number,
                'signature_path' => SignatureStorage::storeDeletionRequest($signatureData, 'household-'.$household->id),
                'status' => PermanentDeletionRequestStatus::Pending,
            ]);
        });
    }

    public function approve(PermanentDeletionRequest $deletionRequest, User $admin): void
    {
        if (! $deletionRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Permintaan ini sudah diproses.',
            ]);
        }

        DB::transaction(function () use ($deletionRequest, $admin) {
            $deletionRequest->refresh();

            if ($deletionRequest->target_type === 'household') {
                $household = Household::query()->find($deletionRequest->household_id);
                if (! $household) {
                    throw ValidationException::withMessages([
                        'target' => 'Kartu keluarga tidak ditemukan atau sudah dihapus.',
                    ]);
                }
                $this->deletionService->deleteHousehold($household);
            } else {
                $resident = Resident::query()->find($deletionRequest->resident_id);
                if (! $resident) {
                    throw ValidationException::withMessages([
                        'target' => 'Data warga tidak ditemukan atau sudah dihapus.',
                    ]);
                }
                $this->deletionService->deleteResident($resident);
            }

            $deletionRequest->update([
                'status' => PermanentDeletionRequestStatus::Approved,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
        });
    }

    public function reject(PermanentDeletionRequest $deletionRequest, User $admin, ?string $notes): void
    {
        if (! $deletionRequest->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Permintaan ini sudah diproses.',
            ]);
        }

        $deletionRequest->update([
            'status' => PermanentDeletionRequestStatus::Rejected,
            'admin_notes' => filled($notes) ? trim($notes) : null,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);
    }

    public function pendingCount(): int
    {
        return PermanentDeletionRequest::query()->pending()->count();
    }

    private function assertNoPendingRequest(string $targetType, ?int $residentId, ?int $householdId): void
    {
        $query = PermanentDeletionRequest::query()->pending()->where('target_type', $targetType);

        if ($targetType === 'resident') {
            $query->where('resident_id', $residentId);
        } else {
            $query->where('household_id', $householdId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'delete' => 'Sudah ada pengajuan hapus permanen yang menunggu persetujuan admin.',
            ]);
        }
    }

    private function generateRequestNumber(?RtProfile $rtProfile): string
    {
        $rtPart = $rtProfile?->rt_number ? str_pad($rtProfile->rt_number, 3, '0', STR_PAD_LEFT) : '000';
        $datePart = now('Asia/Jayapura')->format('Ymd');
        $prefix = "DEL-RT{$rtPart}-{$datePart}";

        $latest = PermanentDeletionRequest::query()
            ->where('request_number', 'like', $prefix.'%')
            ->orderByDesc('request_number')
            ->value('request_number');

        $sequence = 1;
        if (is_string($latest) && preg_match('/(\d{4})$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
