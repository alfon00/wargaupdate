<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    private function createRtProfile(): RtProfile
    {
        return RtProfile::create([
            'slug' => 'rt-001',
            'rt_number' => '001',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);
    }

    private function createRtStaff(RtProfile $profile): User
    {
        return User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-rt@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);
    }

    public function test_rt_staff_can_delete_own_avatar(): void
    {
        Storage::fake('public');

        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);

        $path = 'avatars/'.$staff->id.'.jpg';
        Storage::disk('public')->put($path, 'fake-avatar-content');
        $staff->update(['avatar_path' => $path]);
        $profile->update(['logo_path' => Storage::disk('public')->url($path)]);

        Storage::disk('public')->assertExists($path);

        $this->actingAs($staff)
            ->from(route('rt.profile'))
            ->delete(route('rt.profile.avatar.destroy'))
            ->assertRedirect(route('rt.profile'))
            ->assertSessionHas('success');

        $staff->refresh();
        $profile->refresh();

        $this->assertNull($staff->avatar_path);
        Storage::disk('public')->assertMissing($path);
        $this->assertNull($profile->logo_path);
    }

    public function test_profile_edit_shows_delete_button_when_avatar_exists(): void
    {
        Storage::fake('public');

        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);
        $path = 'avatars/'.$staff->id.'.jpg';
        Storage::disk('public')->put($path, 'fake-avatar-content');
        $staff->update(['avatar_path' => $path]);

        $this->actingAs($staff)
            ->get(route('rt.profile'))
            ->assertOk()
            ->assertSee('Hapus foto', false);
    }

    public function test_profile_edit_hides_delete_button_without_avatar(): void
    {
        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);

        $this->actingAs($staff)
            ->get(route('rt.profile'))
            ->assertOk()
            ->assertDontSee('Hapus foto', false);
    }

    public function test_kelurahan_staff_cannot_use_rt_avatar_destroy_route(): void
    {
        $kelurahan = User::create([
            'name' => 'Staff Kelurahan',
            'email' => 'kelurahan@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $this->actingAs($kelurahan)
            ->delete(route('rt.profile.avatar.destroy'))
            ->assertForbidden();
    }
}
