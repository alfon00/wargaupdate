<?php

namespace App\Services;

use App\Models\Household;
use App\Models\Resident;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RtResidentDeletionService
{
    public function __construct(
        protected ApplicationDeletionService $applicationDeletion,
    ) {}
    /**
     * @return array{allowed: bool, reason: string|null}
     */
    public function canDeleteResident(Resident $resident): array
    {
        $resident->loadMissing('household.residents');

        if ($resident->is_head_of_family) {
            $otherActiveMembers = $resident->household
                ?->residents
                ->where('id', '!=', $resident->id)
                ->filter(fn (Resident $member) => $member->isDomiciledActive())
                ->count() ?? 0;

            if ($otherActiveMembers > 0) {
                return [
                    'allowed' => false,
                    'reason' => 'Kepala KK tidak dapat dihapus selama masih ada anggota aktif lain. Hapus seluruh KK atau tunjuk kepala baru dulu.',
                ];
            }
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * @return array{allowed: bool, reason: string|null}
     */
    public function canDeleteHousehold(Household $household): array
    {
        $household->loadMissing('residents');

        if ($household->residents->isEmpty()) {
            return ['allowed' => true, 'reason' => null];
        }

        foreach ($household->residents as $resident) {
            $check = $this->canDeleteResident($resident);
            if (! $check['allowed']) {
                return [
                    'allowed' => false,
                    'reason' => 'Tidak dapat menghapus KK: '.$check['reason'],
                ];
            }
        }

        return ['allowed' => true, 'reason' => null];
    }

    public function deleteResident(Resident $resident): void
    {
        $check = $this->canDeleteResident($resident);
        if (! $check['allowed']) {
            throw ValidationException::withMessages([
                'delete' => $check['reason'] ?? 'Data warga tidak dapat dihapus.',
            ]);
        }

        DB::transaction(function () use ($resident) {
            $household = $resident->household;
            $this->detachApplicationsFromResident($resident);
            $resident->notificationLogs()->delete();
            $resident->delete();

            if ($household) {
                $this->deleteHouseholdIfEmpty($household);
            }
        });
    }

    public function deleteHousehold(Household $household): void
    {
        $check = $this->canDeleteHousehold($household);
        if (! $check['allowed']) {
            throw ValidationException::withMessages([
                'delete' => $check['reason'] ?? 'Kartu keluarga tidak dapat dihapus.',
            ]);
        }

        DB::transaction(function () use ($household) {
            $household->load('residents');

            foreach ($household->residents as $resident) {
                $this->detachApplicationsFromResident($resident);
                $resident->notificationLogs()->delete();
            }

            $household->residents()->delete();
            $household->pendataanDocuments()->delete();
            $household->delete();
        });
    }

    private function deleteHouseholdIfEmpty(Household $household): void
    {
        if ($household->residents()->exists()) {
            return;
        }

        $household->pendataanDocuments()->delete();
        $household->delete();
    }

    private function detachApplicationsFromResident(Resident $resident): void
    {
        $resident->loadMissing('applications');

        foreach ($resident->applications as $application) {
            $this->applicationDeletion->delete($application);
        }
    }
}
