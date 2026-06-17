<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanelProfileContactEmailTest extends TestCase
{
    use RefreshDatabase;

    private function createRtProfile(string $rtNumber = '001'): RtProfile
    {
        return RtProfile::create([
            'slug' => 'rt-'.$rtNumber,
            'rt_number' => $rtNumber,
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT '.$rtNumber,
        ]);
    }

    private function createRtStaff(RtProfile $profile, string $email = 'ketua-rt@test.local'): User
    {
        return User::create([
            'name' => 'Ketua RT',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);
    }

    public function test_public_profile_hides_login_email_when_it_matches_rt_profile_email(): void
    {
        $profile = $this->createRtProfile();
        $loginEmail = 'ketua-001@test.local';
        $this->createRtStaff($profile, $loginEmail);

        $profile->update(['email' => $loginEmail]);

        $this->get(route('profile.show', $profile))
            ->assertOk()
            ->assertDontSee($loginEmail, false);
    }

    public function test_public_profile_shows_valid_contact_email(): void
    {
        $profile = $this->createRtProfile();
        $this->createRtStaff($profile, 'ketua-001@test.local');

        $contactEmail = 'kontak@rt001.example';
        $profile->update(['email' => $contactEmail]);

        $this->get(route('profile.show', $profile))
            ->assertOk()
            ->assertSee($contactEmail, false);
    }

    public function test_rt_staff_can_update_contact_email_from_panel(): void
    {
        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);

        $this->actingAs($staff)
            ->put(route('rt.profile.update'), [
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => '081234567890',
                'contact_email' => 'kontak@rt001.example',
            ])
            ->assertRedirect(route('rt.profile'));

        $profile->refresh();
        $staff->refresh();

        $this->assertSame('kontak@rt001.example', $profile->email);
        $this->assertSame('ketua-rt@test.local', $staff->email);
    }

    public function test_contact_email_cannot_match_login_email(): void
    {
        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);

        $this->actingAs($staff)
            ->from(route('rt.profile'))
            ->put(route('rt.profile.update'), [
                'name' => $staff->name,
                'email' => $staff->email,
                'contact_email' => $staff->email,
            ])
            ->assertSessionHasErrors('contact_email');
    }

    public function test_rt_staff_profile_update_rejects_invalid_phone(): void
    {
        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);

        $this->actingAs($staff)
            ->from(route('rt.profile'))
            ->put(route('rt.profile.update'), [
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => '0812345678',
            ])
            ->assertSessionHasErrors('phone');
    }

    public function test_rt_staff_can_save_profile_with_unchanged_legacy_phone(): void
    {
        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);
        $staff->update(['phone' => '0812345678']);

        $this->actingAs($staff)
            ->from(route('rt.profile'))
            ->put(route('rt.profile.update'), [
                'name' => 'Ketua RT Diperbarui',
                'email' => $staff->email,
                'phone' => '0812345678',
            ])
            ->assertRedirect(route('rt.profile'));

        $this->assertSame('Ketua RT Diperbarui', $staff->fresh()->name);
    }

    public function test_rt_staff_profile_update_rejects_changing_to_invalid_phone(): void
    {
        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);
        $staff->update(['phone' => '081234567890']);

        $this->actingAs($staff)
            ->from(route('rt.profile'))
            ->put(route('rt.profile.update'), [
                'name' => $staff->name,
                'email' => $staff->email,
                'phone' => '0812345678',
            ])
            ->assertSessionHasErrors('phone');
    }

    public function test_profile_edit_does_not_nest_delete_form_inside_update_form(): void
    {
        Storage::fake('public');

        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);
        $path = 'avatars/'.$staff->id.'.jpg';
        Storage::disk('public')->put($path, 'fake-avatar-content');
        $staff->update(['avatar_path' => $path]);

        $html = $this->actingAs($staff)
            ->get(route('rt.profile'))
            ->assertOk()
            ->getContent();

        $updateFormStart = strpos($html, '<form method="POST" action="'.route('rt.profile.update').'"');
        $updateFormEnd = strpos($html, '</form>', $updateFormStart);
        $deleteFormStart = strpos($html, 'id="profile-avatar-delete-form"');

        $this->assertNotFalse($updateFormStart);
        $this->assertNotFalse($deleteFormStart);
        $this->assertNotFalse($updateFormEnd);
        $this->assertGreaterThan($updateFormEnd, $deleteFormStart);
        $this->assertStringContainsString('form="profile-avatar-delete-form"', $html);
        $this->assertStringContainsString('lw-panel-profile-photo-wrap', $html);
    }
}
