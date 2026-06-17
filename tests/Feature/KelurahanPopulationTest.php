<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KelurahanPopulationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: RtProfile, 2: Household} */
    private function seedHouseholdWithResidents(RtProfile $profile, array $members, string $familyCardNumber = '3201010101010001'): array
    {
        $kelurahan = User::firstOrCreate(
            ['email' => 'kelurahan-pop@test.local'],
            [
                'name' => 'Staff Kelurahan',
                'password' => Hash::make('password'),
                'role' => UserRole::Kelurahan,
            ]
        );

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => $familyCardNumber,
            'address' => 'Jl. Rekap Test',
            'house_number' => '12',
            'status' => 'aktif',
            'status_rumah_tinggal' => 'Milik sendiri',
            'kondisi_rumah_milik' => 'layak',
            'suku' => 'Amungme',
        ]);

        $isFirst = true;
        foreach ($members as $member) {
            Resident::create(array_merge([
                'household_id' => $household->id,
                'domicile_status' => DomicileStatus::Aktif,
                'is_head_of_family' => $isFirst,
            ], $member));
            $isFirst = false;
        }

        return [$kelurahan, $profile, $household];
    }

    private function createRtProfile(string $rtNumber): RtProfile
    {
        return RtProfile::create([
            'rt_number' => $rtNumber,
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT '.$rtNumber,
        ]);
    }

    public function test_population_index_shows_resident_table_like_rt_panel(): void
    {
        $profile = $this->createRtProfile('008');
        [$kelurahan] = $this->seedHouseholdWithResidents($profile, [
            [
                'nik' => '3201010101010088',
                'name' => 'Warga Stats',
                'birth_date' => '1990-01-01',
                'gender' => 'Laki-laki',
                'religion' => 'Kristen',
                'occupation' => 'Petani/Pekebun',
            ],
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.population.index'))
            ->assertOk()
            ->assertSee('Data warga lengkap', false)
            ->assertSee('Kartu keluarga', false)
            ->assertSee('Warga aktif', false)
            ->assertSee('No. Kartu Keluarga', false)
            ->assertSee('Nama', false)
            ->assertSee('NIK', false)
            ->assertSee('Warga Stats')
            ->assertSee('3201010101010088')
            ->assertSee('lw-rt-page', false)
            ->assertSee('lw-rt-list-toolbar', false)
            ->assertDontSee('Daftar KK', false)
            ->assertDontSee('Subtotal halaman', false)
            ->assertDontSee('Ringkasan per RT', false);
    }

    public function test_active_filter_hides_archived_residents(): void
    {
        $profile = $this->createRtProfile('008');
        [$kelurahan, , $household] = $this->seedHouseholdWithResidents($profile, [
            [
                'nik' => '3201010101010099',
                'name' => 'Budi Aktif',
                'birth_date' => '1990-01-01',
                'gender' => 'Laki-laki',
            ],
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010088',
            'name' => 'Siti Arsip',
            'domicile_status' => DomicileStatus::PindahKeluar,
            'is_head_of_family' => false,
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.population.index', ['filter' => 'aktif']))
            ->assertOk()
            ->assertSee('Budi Aktif')
            ->assertDontSee('Siti Arsip');

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.population.index', ['filter' => 'arsip']))
            ->assertOk()
            ->assertSee('Siti Arsip')
            ->assertDontSee('Budi Aktif');
    }

    public function test_all_rt_shows_residents_from_multiple_rts(): void
    {
        $profile008 = $this->createRtProfile('008');
        $profile009 = $this->createRtProfile('009');

        [$kelurahan] = $this->seedHouseholdWithResidents($profile008, [
            [
                'nik' => '3201010101010001',
                'name' => 'Warga RT 008',
                'birth_date' => '1990-01-01',
                'gender' => 'Laki-laki',
            ],
        ]);

        $this->seedHouseholdWithResidents($profile009, [
            [
                'nik' => '3201010101010002',
                'name' => 'Warga RT 009',
                'birth_date' => '1985-05-05',
                'gender' => 'Perempuan',
            ],
        ], '3201010101010002');

        $allRtHtml = $this->actingAs($kelurahan)
            ->get(route('kelurahan.population.index'))
            ->assertOk()
            ->assertSee('Warga RT 008')
            ->assertSee('Warga RT 009')
            ->assertSee($profile008->displayName())
            ->assertSee($profile009->displayName())
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<table class="lw-panel-table lw-rt-data-residents-table lw-rt-data-residents-table--with-rt">/',
            $allRtHtml,
        );
        $this->assertStringContainsString('lw-rt-data-col-rt', $allRtHtml);
        $this->assertStringContainsString('lw-rt-list-toolbar', $allRtHtml);
    }

    public function test_rt_filter_narrows_results_to_single_rt(): void
    {
        $profile008 = $this->createRtProfile('008');
        $profile009 = $this->createRtProfile('009');

        [$kelurahan] = $this->seedHouseholdWithResidents($profile008, [
            [
                'nik' => '3201010101010001',
                'name' => 'Warga RT 008',
                'birth_date' => '1990-01-01',
                'gender' => 'Laki-laki',
            ],
        ]);

        $this->seedHouseholdWithResidents($profile009, [
            [
                'nik' => '3201010101010002',
                'name' => 'Warga RT 009',
                'birth_date' => '1985-05-05',
                'gender' => 'Perempuan',
            ],
        ], '3201010101010002');

        $singleRtHtml = $this->actingAs($kelurahan)
            ->get(route('kelurahan.population.index', ['rt_profile_id' => $profile008->id]))
            ->assertOk()
            ->assertSee('Warga RT 008')
            ->assertDontSee('Warga RT 009')
            ->assertSee('RT: '.$profile008->displayName(), false)
            ->assertSee('lw-rt-list-toolbar', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<table class="lw-panel-table lw-rt-data-residents-table">/',
            $singleRtHtml,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<table class="[^"]*lw-rt-data-residents-table--with-rt/',
            $singleRtHtml,
        );
    }

    public function test_resident_show_is_read_only_monitoring_page(): void
    {
        $profile = $this->createRtProfile('008');
        [$kelurahan, , $household] = $this->seedHouseholdWithResidents($profile, [
            [
                'nik' => '3201010101010088',
                'name' => 'Kepala KK Detail',
                'birth_date' => '1990-01-01',
                'gender' => 'Laki-laki',
                'religion' => 'Kristen',
                'occupation' => 'Petani/Pekebun',
            ],
        ]);

        $resident = $household->residents()->first();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.data-warga.show', $resident))
            ->assertOk()
            ->assertSee('Kepala KK Detail')
            ->assertSee('mode monitoring', false)
            ->assertSee('Kembali ke data warga', false)
            ->assertDontSee(route('rt.residents.edit', $resident), false);
    }

    public function test_search_by_nik_finds_resident(): void
    {
        $profile = $this->createRtProfile('008');
        [$kelurahan] = $this->seedHouseholdWithResidents($profile, [
            [
                'nik' => '3201010101010099',
                'name' => 'KK Cari NIK',
                'birth_date' => '1990-01-01',
                'gender' => 'Laki-laki',
            ],
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.population.index', ['q' => '3201010101010099', 'filter' => 'semua']))
            ->assertOk()
            ->assertSee('KK Cari NIK');
    }
}
