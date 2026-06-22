<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\KelurahanOfficial;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminKelurahanProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createKelurahanUser(string $email = 'kelurahan-admin@test.local'): User
    {
        return User::create([
            'name' => 'Admin Kelurahan',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);
    }

    private function createRtStaff(): User
    {
        $profile = RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        return User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-rt@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);
    }

    public function test_admin_profile_hub_shows_profile_cards_not_edit_form(): void
    {
        $admin = $this->createKelurahanUser();

        $this->actingAs($admin)
            ->get(route('admin.profile'))
            ->assertOk()
            ->assertSee('lw-panel-profile-hub', false)
            ->assertSee('lw-panel-profile-card', false)
            ->assertSee('Profil akun saya', false)
            ->assertSee('Profil lurah publik', false)
            ->assertSee(route('admin.profile.account.show'), false)
            ->assertSee(route('admin.profile.kelurahan.show'), false)
            ->assertDontSee('id="lurah_jabatan"', false);
    }

    public function test_kelurahan_show_page_has_edit_button(): void
    {
        $admin = $this->createKelurahanUser();

        $this->actingAs($admin)
            ->get(route('admin.profile.kelurahan.show'))
            ->assertOk()
            ->assertSee('Edit profil lurah', false)
            ->assertSee(route('admin.profile.kelurahan.edit'), false);
    }

    public function test_kelurahan_edit_page_shows_form(): void
    {
        $admin = $this->createKelurahanUser();

        $this->actingAs($admin)
            ->get(route('admin.profile.kelurahan.edit'))
            ->assertOk()
            ->assertSee('id="lurah_jabatan"', false)
            ->assertSee('lw-panel-profile-photo', false)
            ->assertSee('lw-panel-profile-upload-label', false)
            ->assertSee(route('admin.profile.kelurahan.update'), false);
    }

    public function test_super_admin_can_update_kelurahan_public_profile(): void
    {
        $admin = $this->createKelurahanUser();

        $this->actingAs($admin)
            ->put(route('admin.profile.kelurahan.update'), [
                'jabatan' => 'Lurah Kelurahan Inauga',
                'nama' => 'Lurah Baru, S.E.',
                'telepon' => '081234567890',
                'email' => 'kantor@kelurahan-inauga.example',
                'alamat_kantor' => 'Kantor Kelurahan Inauga',
                'jam_layanan' => 'Senin–Jumat 08.00–14.00 WIT',
                'visi' => 'Visi baru kelurahan.',
                'misi' => '1. Misi pertama.',
            ])
            ->assertRedirect(route('admin.profile.kelurahan.show'));

        $official = KelurahanOfficial::lurah()->fresh();
        $this->assertSame('Lurah Baru, S.E.', $official->nama);
        $this->assertSame('kantor@kelurahan-inauga.example', $official->email);

        auth()->logout();

        $this->get(route('profile.index'))
            ->assertOk()
            ->assertSee('Lurah Baru, S.E.', false)
            ->assertSee('kantor@kelurahan-inauga.example', false)
            ->assertDontSee($admin->email, false);
    }

    public function test_rt_staff_profile_page_still_shows_edit_form(): void
    {
        $staff = $this->createRtStaff();

        $this->actingAs($staff)
            ->get(route('rt.profile'))
            ->assertOk()
            ->assertSee('Profil saya', false)
            ->assertSee('id="name"', false)
            ->assertDontSee(route('admin.profile.kelurahan.show'), false)
            ->assertDontSee('Profil lurah publik', false);
    }

    public function test_non_super_admin_cannot_update_kelurahan_public_profile(): void
    {
        $staff = $this->createRtStaff();

        $this->actingAs($staff)
            ->put(route('admin.profile.kelurahan.update'), [
                'jabatan' => 'Lurah Palsu',
                'nama' => 'Tidak Boleh',
            ])
            ->assertForbidden();
    }

    public function test_kelurahan_update_rejects_invalid_phone(): void
    {
        $admin = $this->createKelurahanUser();

        $this->actingAs($admin)
            ->from(route('admin.profile.kelurahan.edit'))
            ->put(route('admin.profile.kelurahan.update'), [
                'jabatan' => 'Lurah Kelurahan Inauga',
                'nama' => 'Lurah Baru',
                'telepon' => '0812345678',
            ])
            ->assertSessionHasErrors('telepon');
    }
}
