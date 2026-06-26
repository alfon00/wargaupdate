<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class SyncUserPublicProfile
{
    public function sync(User $user): void
    {
        if ($user->isRtStaff() && $user->rt_profile_id) {
            $this->syncRtProfile($user);
        }
    }

    private function syncRtProfile(User $user): void
    {
        $rt = $user->rtProfile;
        if (! $rt) {
            return;
        }

        if ($user->role === UserRole::KetuaRt) {
            $rt->ketua_rt = $user->name;
        }

        if ($user->phone) {
            $rt->phone = $user->phone;
            $rt->whatsapp = $user->phone;
        }

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            $rt->logo_path = Storage::disk('public')->url($user->avatar_path);
        } else {
            $rt->logo_path = null;
        }

        $rt->save();

        if (! $user->rt_profile_id) {
            $user->rt_profile_id = $rt->id;
            $user->saveQuietly();
        }
    }
}
