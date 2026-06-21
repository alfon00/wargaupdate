<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPanelUiTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        return User::create([
            'name' => 'Admin Sistem',
            'email' => 'super-admin@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
        ]);
    }

    private function createRtProfile(string $rtNumber, string $ketuaRt): RtProfile
    {
        return RtProfile::create([
            'rt_number' => $rtNumber,
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => $ketuaRt,
        ]);
    }

    public function test_admin_pages_render_for_super_admin(): void
    {
        $admin = $this->createSuperAdmin();

        $dashboard = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard operasional')
            ->assertSee('Warga terdata')
            ->assertSee('Akses cepat')
            ->assertSee('Permohonan terbaru')
            ->assertSee('Laporan warga terbaru')
            ->assertSee('Monitoring kependudukan')
            ->assertSee('Penduduk')
            ->assertSee('Jenis kelamin')
            ->assertSee('Monografi kependudukan')
            ->assertSee('Tingkat Pendidikan', false)
            ->assertSee('Monitoring operasional', false)
            ->assertSee('Permohonan surat', false)
            ->assertSee('Data warga lengkap', false)
            ->assertSee('lw-admin-page', false);

        $dashboard->assertDontSee('Pengguna per peran', false)
            ->assertDontSee('Pengguna terbaru', false)
            ->assertDontSee('Perlu perhatian', false)
            ->assertDontSee('Profil Lurah', false)
            ->assertDontSee('lw-admin-nav-group-label">Akun', false)
            ->assertSee('lw-panel-user-link', false)
            ->assertSee(route('admin.profile'), false);

        $this->actingAs($admin)
            ->get(route('admin.profile'))
            ->assertOk()
            ->assertSee('lw-panel-profile-hub', false)
            ->assertSee(route('admin.profile.account.show'), false);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Pengguna')
            ->assertSee('lw-admin-page', false);

        $this->actingAs($admin)
            ->get(route('admin.rt-profiles.index'))
            ->assertOk()
            ->assertSee('Profil RT');

        $this->actingAs($admin)
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('Katalog layanan');

        $this->actingAs($admin)
            ->get('/admin/lurah')
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.deletion-requests.index'))
            ->assertOk()
            ->assertSee('Permintaan hapus permanen')
            ->assertSee('lw-admin-page', false);
    }

    public function test_user_search_filters_by_name_or_email(): void
    {
        $admin = $this->createSuperAdmin();
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
        ]);
        User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['q' => 'Budi']))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Siti Aminah');
    }

    public function test_user_index_delete_button_shows_hapus_not_role_label(): void
    {
        $admin = $this->createSuperAdmin();
        User::create([
            'name' => 'Sekretaris Contoh',
            'email' => 'sekretaris@example.test',
            'password' => Hash::make('password'),
            'role' => UserRole::SekretarisRt,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Sekretaris RT', false)
            ->assertSee('>Hapus</button>', false)
            ->assertDontSee('lw-panel-table-link--danger">Sekretaris RT</button>', false);
    }

    public function test_rt_profile_search_filters_by_rt_number(): void
    {
        $admin = $this->createSuperAdmin();
        $this->createRtProfile('008', 'Ketua Delapan');
        $this->createRtProfile('009', 'Ketua Sembilan');

        $this->actingAs($admin)
            ->get(route('admin.rt-profiles.index', ['q' => '008']))
            ->assertOk()
            ->assertSee('RT 008')
            ->assertDontSee('RT 009');
    }

    public function test_super_admin_can_access_kelurahan_monitoring_routes(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)
            ->get(route('kelurahan.dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('kelurahan.applications.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('kelurahan.population.index'))
            ->assertOk()
            ->assertSee('Data warga lengkap', false)
            ->assertSee('No. Kartu Keluarga', false)
            ->assertDontSee('Data Penduduk per RT', false)
            ->assertDontSee('Daftar KK', false);
    }

    public function test_kelurahan_user_still_can_access_monitoring_routes(): void
    {
        $kelurahan = User::create([
            'name' => 'Petugas Kelurahan',
            'email' => 'kelurahan@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.dashboard'))
            ->assertOk();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.index'))
            ->assertOk();
    }

    public function test_rt_staff_cannot_access_kelurahan_monitoring_routes(): void
    {
        $profile = $this->createRtProfile('002', 'Ketua Dua');
        $staff = User::create([
            'name' => 'Ketua RT 002',
            'email' => 'ketua-002@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $this->actingAs($staff)
            ->get(route('kelurahan.dashboard'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('kelurahan.applications.index'))
            ->assertForbidden();
    }

    public function test_rt_panel_not_regressed_for_ketua_rt(): void
    {
        $profile = $this->createRtProfile('001', 'Ketua Satu');
        $staff = User::create([
            'name' => 'Ketua RT 001',
            'email' => 'ketua-001@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $this->actingAs($staff)
            ->get(route('rt.dashboard'))
            ->assertOk();
    }
}
