<?php

namespace App\Http\Controllers\Rt\Concerns;

use App\Models\Application;
use App\Models\CitizenReport;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;

trait ResolvesRtProfile
{
    protected function resolvedRtProfile(): ?RtProfile
    {
        return RtProfile::forRtStaffUser(auth()->user());
    }

    protected function requireRtProfile(): RtProfile
    {
        $rt = $this->resolvedRtProfile();
        abort_unless($rt, 403, 'Akun belum terhubung ke profil RT. Hubungi admin untuk menetapkan RT.');

        return $rt;
    }

    protected function abortUnlessOwnsApplication(Application $application): void
    {
        $rt = $this->requireRtProfile();
        $application->loadMissing('resident.household');
        $profileIds = RtProfile::profileIdsForRtNumber($rt->rt_number);

        $assignedRtId = (int) $application->rt_profile_id;
        if ($assignedRtId && in_array($assignedRtId, $profileIds, true)) {
            return;
        }

        $householdRtId = (int) $application->resident?->household?->rt_profile_id;

        abort_unless(
            $householdRtId && in_array($householdRtId, $profileIds, true),
            404
        );
    }

    protected function abortUnlessOwnsResident(Resident $resident): void
    {
        $rt = $this->requireRtProfile();
        $resident->loadMissing('household');
        $householdRtId = (int) $resident->household?->rt_profile_id;

        abort_unless(
            $householdRtId && RtProfile::householdBelongsToRtNumber($householdRtId, $rt->rt_number),
            404
        );
    }

    protected function abortUnlessOwnsHousehold(Household $household): void
    {
        $rt = $this->requireRtProfile();

        abort_unless(
            RtProfile::householdBelongsToRtNumber((int) $household->rt_profile_id, $rt->rt_number),
            404
        );
    }

    protected function abortUnlessOwnsReport(CitizenReport $report): void
    {
        $rt = $this->requireRtProfile();

        abort_unless(
            in_array((int) $report->rt_profile_id, RtProfile::profileIdsForRtNumber($rt->rt_number), true),
            404
        );
    }
}
