<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PanelUiTest extends TestCase
{
    use RefreshDatabase;

    private function createRtProfile(string $rtNumber = '001'): RtProfile
    {
        return RtProfile::create([
            'rt_number' => $rtNumber,
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT '.$rtNumber,
        ]);
    }

    private function createRtStaff(?RtProfile $profile = null): User
    {
        $profile ??= $this->createRtProfile();

        return User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-rt@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);
    }

    public function test_rt_dashboard_and_index_pages_render(): void
    {
        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);

        $this->actingAs($staff)
            ->get(route('rt.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard RT')
            ->assertSee('lw-panel-page-title', false)
            ->assertSee('lw-rt-page', false)
            ->assertSee('Dashboard', false)
            ->assertSee($profile->displayName(), false)
            ->assertSee('lw-panel-stats', false)
            ->assertSee('Warga aktif')
            ->assertSee('Akses cepat')
            ->assertDontSee('Penduduk')
            ->assertDontSee('Jenis kelamin')
            ->assertDontSee('Gender ratio', false)
            ->assertSee('lw-panel-date', false)
            ->assertDontSee('Monografi kependudukan')
            ->assertSee('Semua permohonan →', false)
            ->assertSee('Aktivitas terbaru')
            ->assertDontSee('Ringkasan aktivitas terbaru pengurus RT')
            ->assertDontSee('Templat rekap kependudukan')
            ->assertDontSee('Tingkat Pendidikan', false)
            ->assertDontSee('Warga Aktif Terdaftar')
            ->assertDontSee('Komposisi kepala keluarga')
            ->assertDontSee('Aksi cepat')
            ->assertDontSee('Perlu tindakan hari ini')
            ->assertDontSee('Prioritas kerja hari ini')
            ->assertDontSee('pengaduan terbaru', false);

        $this->actingAs($staff)
            ->get(route('rt.applications.index'))
            ->assertOk()
            ->assertSee('Daftar Permohonan')
            ->assertSee('lw-rt-page', false);

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index'))
            ->assertOk()
            ->assertSee('Data warga lengkap')
            ->assertSee('lw-rt-page', false);

        $this->actingAs($staff)
            ->get(route('rt.pendataan.index'))
            ->assertOk()
            ->assertSee('Pendataan masuk')
            ->assertSee('lw-rt-page', false)
            ->assertSee('lw-panel-table--rt-list', false);

        $this->actingAs($staff)
            ->get(route('rt.reports.index'))
            ->assertOk()
            ->assertSee('Laporan warga')
            ->assertSee('lw-rt-page', false)
            ->assertSee('lw-panel-table--rt-list', false);

        $this->actingAs($staff)
            ->get(route('rt.pengumuman.index'))
            ->assertOk()
            ->assertSee('Pengumuman')
            ->assertSee('lw-rt-page', false);

        $this->actingAs($staff)
            ->get(route('rt.notifications.index'))
            ->assertOk()
            ->assertSee('Log Notifikasi WhatsApp')
            ->assertSee('lw-rt-page', false);

        $this->actingAs($staff)
            ->get(route('rt.data-warga.create'))
            ->assertOk()
            ->assertSee('Daftar KK & warga')
            ->assertSee('lw-rt-page', false)
            ->assertSee('lw-panel-form-grid--labeled', false)
            ->assertSee('lw-panel-form--labeled', false);

        $this->actingAs($staff)
            ->get(route('rt.pengumuman.create'))
            ->assertOk()
            ->assertSee('lw-panel-form--labeled', false);
    }

    public function test_rt_panel_sidebar_shows_today_date_in_long_format(): void
    {
        $this->travelTo(Carbon::parse('2026-06-11 10:00:00', 'Asia/Jayapura'));

        $staff = $this->createRtStaff();

        $this->actingAs($staff)
            ->get(route('rt.dashboard'))
            ->assertOk()
            ->assertSee('lw-panel-date', false)
            ->assertSee('Hari ini', false)
            ->assertSee('Kamis, 11 Juni 2026', false)
            ->assertDontSee('lw-nav-date', false)
            ->assertDontSee('lw-panel-topbar-date', false);
    }

    public function test_kelurahan_dashboard_and_index_pages_render(): void
    {
        $kelurahan = User::create([
            'name' => 'Staff Kelurahan',
            'email' => 'kelurahan@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Monitoring')
            ->assertSee('Laporan warga terbaru')
            ->assertSee('lw-kel-page', false);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.index'))
            ->assertOk()
            ->assertSee('Daftar Permohonan')
            ->assertSee('lw-kel-page', false);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.population.index'))
            ->assertOk()
            ->assertSee('Data warga lengkap', false);
    }

    public function test_admin_panel_still_renders_after_shared_components(): void
    {
        $admin = User::create([
            'name' => 'Admin Sistem',
            'email' => 'super-admin@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard operasional')
            ->assertSee('Akses cepat');

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Pengguna');
    }

    public function test_rt_application_search_filters_by_query(): void
    {
        $profile = $this->createRtProfile('008');
        $staff = $this->createRtStaff($profile);

        $this->actingAs($staff)
            ->get(route('rt.applications.index', ['q' => 'test']))
            ->assertOk();
    }

    public function test_panel_sidebar_profile_link_in_footer(): void
    {
        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);

        $this->actingAs($staff)
            ->get(route('rt.dashboard'))
            ->assertOk()
            ->assertDontSee('lw-panel-nav-group-label">Akun', false)
            ->assertSee('lw-panel-user-link', false)
            ->assertSee(route('rt.profile'), false);

        $kelurahan = User::create([
            'name' => 'Staff Kelurahan',
            'email' => 'kelurahan-profile@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.dashboard'))
            ->assertOk()
            ->assertDontSee('lw-panel-nav-group-label">Akun', false)
            ->assertSee('lw-panel-user-link', false)
            ->assertSee(route('kelurahan.profile'), false);
    }
}
