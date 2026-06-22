<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\User;
use App\Support\RtPopulationAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RtDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function createRtProfile(string $rtNumber = '008'): RtProfile
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

    private function createRtStaff(RtProfile $profile): User
    {
        return User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-analytics@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);
    }

    private function createKelurahanUser(): User
    {
        return User::create([
            'name' => 'Admin Kelurahan',
            'email' => 'super-admin-analytics@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);
    }

    public function test_analytics_aggregates_active_residents(): void
    {
        $profile = $this->createRtProfile();
        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010001',
            'address' => 'Jl. Merpati',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'name' => 'Budi',
            'gender' => 'Laki-laki',
            'education' => 'SD',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);
        Resident::create([
            'household_id' => $household->id,
            'name' => 'Siti',
            'gender' => 'Perempuan',
            'education' => 'TK',
            'domicile_status' => DomicileStatus::Aktif,
        ]);
        Resident::create([
            'household_id' => $household->id,
            'name' => 'Arsip',
            'domicile_status' => DomicileStatus::PindahKeluar,
        ]);

        $analytics = RtPopulationAnalytics::forRtProfile($profile);

        $this->assertSame(2, $analytics['population']['total']);
        $this->assertSame(1, $analytics['population']['households']);
        $this->assertSame(2, $analytics['population']['classified']);
        $this->assertSame(1, $analytics['gender']['L']);
        $this->assertSame(1, $analytics['gender']['P']);
        $this->assertSame(1, $analytics['education']['SD']);
        $this->assertSame(1, $analytics['education']['TK']);
    }

    public function test_kelurahan_analytics_aggregates_all_rt_residents(): void
    {
        $profileA = $this->createRtProfile('001');
        $profileB = $this->createRtProfile('008');

        $householdA = Household::create([
            'rt_profile_id' => $profileA->id,
            'family_card_number' => '3201010101010010',
        ]);
        $householdB = Household::create([
            'rt_profile_id' => $profileB->id,
            'family_card_number' => '3201010101010011',
        ]);

        Resident::create([
            'household_id' => $householdA->id,
            'name' => 'Andi',
            'gender' => 'Laki-laki',
            'education' => 'SD',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);
        Resident::create([
            'household_id' => $householdB->id,
            'name' => 'Budi',
            'gender' => 'Perempuan',
            'education' => 'TK',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $analytics = RtPopulationAnalytics::forKelurahan();

        $this->assertSame(2, $analytics['population']['total']);
        $this->assertSame(2, $analytics['population']['households']);
        $this->assertSame(1, $analytics['gender']['L']);
        $this->assertSame(1, $analytics['gender']['P']);
    }

    public function test_admin_dashboard_shows_analytics_widgets_with_data(): void
    {
        $profile = $this->createRtProfile();
        $admin = $this->createKelurahanUser();
        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010002',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'name' => 'Ahmad',
            'gender' => 'Laki-laki',
            'education' => 'SMA/SMK',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);
        Resident::create([
            'household_id' => $household->id,
            'name' => 'Rina',
            'gender' => 'Perempuan',
            'education' => 'SMP',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard operasional')
            ->assertSee('Monitoring kependudukan')
            ->assertSee('Penduduk')
            ->assertSee('Jenis kelamin')
            ->assertDontSee('Gender ratio', false)
            ->assertSee('2 jiwa')
            ->assertSee('1 KK')
            ->assertSee('1 : 1', false)
            ->assertSee('Monografi kependudukan')
            ->assertSee('Tingkat Pendidikan', false);
    }

    public function test_rt_dashboard_does_not_show_population_monitoring(): void
    {
        $profile = $this->createRtProfile();
        $staff = $this->createRtStaff($profile);

        $this->actingAs($staff)
            ->get(route('rt.dashboard'))
            ->assertOk()
            ->assertDontSee('Monografi kependudukan')
            ->assertDontSee('Monitoring kependudukan')
            ->assertDontSee('Penduduk');
    }

    public function test_admin_dashboard_shows_empty_analytics_when_no_residents(): void
    {
        $admin = $this->createKelurahanUser();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Penduduk')
            ->assertSee('Belum ada warga aktif')
            ->assertSee('angka monografi ditampilkan sebagai «—»')
            ->assertSee('lw-rt-analytics-donut-wrap', false);
    }

    public function test_admin_dashboard_shows_partial_gender_empty_when_gender_missing(): void
    {
        $profile = $this->createRtProfile();
        $admin = $this->createKelurahanUser();
        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010005',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'name' => 'Tanpa Gender',
            'gender' => null,
            'education' => 'SD',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('1 jiwa')
            ->assertSee('jenis kelamin belum diisi');
    }

    public function test_admin_dashboard_shows_education_empty_when_all_unclassified(): void
    {
        $profile = $this->createRtProfile();
        $admin = $this->createKelurahanUser();
        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010006',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'name' => 'Tanpa Pendidikan',
            'gender' => 'Laki-laki',
            'education' => null,
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Data pendidikan belum diisi')
            ->assertSee('1 jiwa');
    }

    public function test_education_bucket_mapping(): void
    {
        $this->assertSame('SLTP', RtPopulationAnalytics::educationBucket('SMP'));
        $this->assertSame('SLTA', RtPopulationAnalytics::educationBucket('SMA/SMK'));
        $this->assertSame('PT', RtPopulationAnalytics::educationBucket('S1'));
        $this->assertSame('other', RtPopulationAnalytics::educationBucket('Tidak sekolah'));
    }

    public function test_monograph_table_aggregates_row_for_rt(): void
    {
        $profile = $this->createRtProfile('008');
        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010003',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'name' => 'Budi',
            'gender' => 'Laki-laki',
            'education' => 'SD',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);
        Resident::create([
            'household_id' => $household->id,
            'name' => 'Siti',
            'gender' => 'Perempuan',
            'education' => 'TK',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $monograph = RtPopulationAnalytics::monographTable($profile);

        $this->assertSame(8, $monograph['highlight_row']);
        $this->assertSame(1, $monograph['rows'][8]['L']);
        $this->assertSame(1, $monograph['rows'][8]['P']);
        $this->assertSame(2, $monograph['rows'][8]['jiwa']);
        $this->assertSame(1, $monograph['rows'][8]['kk']);
        $this->assertSame(1, $monograph['rows'][8]['SD']);
        $this->assertSame(1, $monograph['rows'][8]['TK']);
        $this->assertSame(2, $monograph['rows'][8]['jumlah']);
        $this->assertSame(2, $monograph['totals']['jiwa']);
        $this->assertSame(1, $monograph['totals']['kk']);
        $this->assertSame(0, $monograph['rows'][1]['jiwa']);
    }

    public function test_admin_dashboard_monograph_table_renders_full_table(): void
    {
        $profile = $this->createRtProfile('008');
        $admin = $this->createKelurahanUser();
        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010004',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'name' => 'Ahmad',
            'gender' => 'Laki-laki',
            'education' => 'SMA/SMK',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);
        Resident::create([
            'household_id' => $household->id,
            'name' => 'Rina',
            'gender' => 'Perempuan',
            'education' => 'SMP',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Monografi kependudukan')
            ->assertSee('Rekap seluruh RT — monitoring admin.')
            ->assertSee('<th scope="row" class="lw-rt-monograph-col-rt">Total</th>', false)
            ->assertSee('aria-label="Jiwa RT 08">2<', false)
            ->assertSee('aria-label="Total jiwa">2<', false);
    }
}
