<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RtApplicationStampTest extends TestCase
{
    use RefreshDatabase;

    private function createRtStaff(): array
    {
        $profile = RtProfile::create([
            'slug' => 'rt-008',
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 008',
            'email' => 'ketua008@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        return [$profile, $staff];
    }

    public function test_applications_index_shows_settings_button_and_stamp_modal(): void
    {
        [, $staff] = $this->createRtStaff();

        $this->withoutVite();

        $html = $this->actingAs($staff)
            ->get(route('rt.applications.index'))
            ->assertOk()
            ->assertSee('data-rt-stamp-settings-open', false)
            ->assertSee('Pengaturan', false)
            ->assertSee('id="lw-rt-stamp-settings-modal"', false)
            ->assertSee('Pengaturan cap surat', false)
            ->assertSee('name="stamp"', false)
            ->getContent();

        $this->assertStringContainsString('lw-rt-stamp-settings-modal', $html);
        $this->assertMatchesRegularExpression('/id="lw-rt-stamp-settings-modal"[^>]*hidden/i', $html);
        $this->assertDoesNotMatchRegularExpression('/<article[^>]*>[\s\S]*Cap \/ stempel resmi RT/i', $html);
    }

    public function test_rt_staff_can_upload_stamp_from_applications_menu(): void
    {
        Storage::fake('public');
        [$profile, $staff] = $this->createRtStaff();

        $file = UploadedFile::fake()->image('cap-rt.png', 120, 120);

        $this->actingAs($staff)
            ->post(route('rt.applications.stamp.update'), [
                'stamp' => $file,
            ])
            ->assertRedirect(route('rt.applications.index'));

        $profile->refresh();
        $this->assertNotNull($profile->stamp_path);
        Storage::disk('public')->assertExists($profile->stamp_path);
    }

    public function test_rt_staff_can_delete_stamp_from_applications_menu(): void
    {
        Storage::fake('public');
        [$profile, $staff] = $this->createRtStaff();
        $path = 'stamps/rt-008.png';
        Storage::disk('public')->put($path, 'stamp-content');
        $profile->update(['stamp_path' => $path]);

        $this->actingAs($staff)
            ->delete(route('rt.applications.stamp.destroy'))
            ->assertRedirect(route('rt.applications.index'));

        $profile->refresh();
        $this->assertNull($profile->stamp_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_profile_page_does_not_show_stamp_upload_section(): void
    {
        [, $staff] = $this->createRtStaff();

        $this->actingAs($staff)
            ->get(route('rt.profile'))
            ->assertOk()
            ->assertDontSee('Pengaturan cap surat', false);
    }

    public function test_stamp_validation_error_reopens_modal(): void
    {
        [, $staff] = $this->createRtStaff();

        $this->withoutVite();

        $html = $this->actingAs($staff)
            ->from(route('rt.applications.index'))
            ->followingRedirects()
            ->post(route('rt.applications.stamp.update'), [])
            ->assertOk()
            ->assertSee('Pengaturan cap surat', false)
            ->getContent();

        $this->assertStringContainsString('id="lw-rt-stamp-settings-modal"', $html);
        $this->assertDoesNotMatchRegularExpression('/id="lw-rt-stamp-settings-modal"[^>]*hidden/i', $html);
    }
}
