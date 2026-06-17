<?php

namespace Tests\Feature;

use App\Enums\RtPublicationType;
use App\Enums\UserRole;
use App\Models\RtProfile;
use App\Models\RtPublication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KelurahanPublicationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: RtProfile, 1: RtProfile, 2: RtPublication, 3: RtPublication} */
    private function seedPublications(): array
    {
        $rtA = RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        $rtB = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $kegiatanA = RtPublication::create([
            'rt_profile_id' => $rtA->id,
            'type' => RtPublicationType::Kegiatan,
            'judul' => 'Kerja Bakti RT 001',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Balai RT',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $kegiatanB = RtPublication::create([
            'rt_profile_id' => $rtB->id,
            'type' => RtPublicationType::Kegiatan,
            'judul' => 'Senam Pagi RT 008',
            'tanggal' => now()->toDateString(),
            'lokasi' => 'Lapangan',
            'is_published' => true,
            'published_at' => now(),
        ]);

        return [$rtA, $rtB, $kegiatanA, $kegiatanB];
    }

    private function createKelurahanUser(): User
    {
        return User::create([
            'name' => 'Petugas Kelurahan',
            'email' => 'kelurahan-pub@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);
    }

    private function createSuperAdmin(): User
    {
        return User::create([
            'name' => 'Admin Sistem',
            'email' => 'super-admin-pub@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
        ]);
    }

    public function test_kelurahan_can_monitor_kegiatan_from_all_rt(): void
    {
        [$rtA, $rtB, $kegiatanA, $kegiatanB] = $this->seedPublications();
        $kelurahan = $this->createKelurahanUser();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.kegiatan.index'))
            ->assertOk()
            ->assertSee('Kerja Bakti RT 001', false)
            ->assertSee('Senam Pagi RT 008', false)
            ->assertSee($rtA->displayName(), false)
            ->assertSee($rtB->displayName(), false)
            ->assertSee(route('kelurahan.pengumuman.index'), false)
            ->assertDontSee('Edit', false);
    }

    public function test_super_admin_can_monitor_kegiatan(): void
    {
        $this->seedPublications();
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)
            ->get(route('kelurahan.kegiatan.index'))
            ->assertOk()
            ->assertSee('Kerja Bakti RT 001', false);
    }

    public function test_kegiatan_index_filters_by_rt_profile(): void
    {
        [$rtA, , $kegiatanA] = $this->seedPublications();
        $kelurahan = $this->createKelurahanUser();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.kegiatan.index', ['rt_profile_id' => $rtA->id]))
            ->assertOk()
            ->assertSee($kegiatanA->judul, false)
            ->assertDontSee('Senam Pagi RT 008', false);
    }

    public function test_rt_staff_cannot_access_kelurahan_publication_monitoring(): void
    {
        $this->seedPublications();

        $profile = RtProfile::where('rt_number', '001')->firstOrFail();
        $staff = User::create([
            'name' => 'Ketua RT 001',
            'email' => 'ketua001-pub@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $this->actingAs($staff)
            ->get(route('kelurahan.kegiatan.index'))
            ->assertForbidden();
    }

    public function test_admin_sidebar_includes_kegiatan_menu_link(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Kegiatan &amp; pengumuman', false)
            ->assertSee(route('kelurahan.kegiatan.index'), false);
    }

    public function test_kelurahan_sidebar_includes_kegiatan_menu_link(): void
    {
        $kelurahan = $this->createKelurahanUser();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.dashboard'))
            ->assertOk()
            ->assertSee('Kegiatan &amp; pengumuman', false)
            ->assertSee(route('kelurahan.kegiatan.index'), false);
    }
}
