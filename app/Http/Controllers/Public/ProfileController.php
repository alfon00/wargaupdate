<?php

namespace App\Http\Controllers\Public;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $profiles = RtProfile::inauga()
            ->withRegisteredStaff()
            ->with(['users' => fn ($q) => $q->where('role', UserRole::KetuaRt)])
            ->orderByDesc('id')
            ->get()
            ->unique('rt_number')
            ->sortBy('rt_number', SORT_NATURAL)
            ->values();

        $residentCounts = RtProfile::activeResidentCountsForProfiles($profiles);

        $highlightSlug = null;
        $requestedSlug = request('rt');
        if ($requestedSlug) {
            $match = $profiles->firstWhere('slug', $requestedSlug)
                ?? $profiles->firstWhere('rt_number', preg_replace('/\D/', '', $requestedSlug));
            if ($match) {
                $highlightSlug = $match->slug;
            }
        }

        return view('public.profile', [
            'profiles' => $profiles,
            'residentCounts' => $residentCounts,
            'highlightSlug' => $highlightSlug,
        ]);
    }

    public function show(RtProfile $rtProfile): View
    {
        abort_unless(
            RtProfile::inauga()->where('id', $rtProfile->id)->exists(),
            404
        );

        $rtProfile->load(['users' => fn ($q) => $q->where('role', UserRole::KetuaRt)]);
        abort_unless($rtProfile->registeredStaffCount() > 0, 404);

        $residentCount = $rtProfile->activeResidentCount();

        return view('public.profile-show', compact('rtProfile', 'residentCount'));
    }

    public function showStaff(RtProfile $rtProfile, User $user): View
    {
        abort_unless(
            RtProfile::inauga()->where('id', $rtProfile->id)->exists(),
            404
        );
        abort_unless($user->appearsOnPublicRtProfile(), 404);
        abort_unless((int) $user->rt_profile_id === (int) $rtProfile->id, 404);
        abort_unless($user->isRtStaff(), 404);

        $roleLabel = 'Ketua RT';

        return view('public.profile-staff-show', compact('rtProfile', 'user', 'roleLabel'));
    }
}
