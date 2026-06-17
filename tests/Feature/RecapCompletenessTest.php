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

class RecapCompletenessTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: RtProfile, 2: Household, 3: User} */
    private function createRtContext(): array
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-recap@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010001',
            'address' => 'Jl. Test',
            'status' => 'aktif',
        ]);

        $kelurahan = User::create([
            'name' => 'Staff Kelurahan',
            'email' => 'kelurahan-recap@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        return [$staff, $profile, $household, $kelurahan];
    }

    public function test_rt_cannot_save_household_without_recap_fields(): void
    {
        [$staff, $profile, $household] = $this->createRtContext();

        $this->actingAs($staff)
            ->put(route('rt.households.update', $household), [
                'family_card_number' => $household->family_card_number,
                'address' => 'Jl. Baru',
            ])
            ->assertSessionHasErrors(['status_rumah_tinggal']);

        $this->actingAs($staff)
            ->put(route('rt.households.update', $household), [
                'family_card_number' => $household->family_card_number,
                'address' => 'Jl. Baru',
                'status_rumah_tinggal' => 'Milik sendiri',
                'suku' => 'Amungme',
            ])
            ->assertSessionHasErrors(['kondisi_rumah_milik']);
    }

    public function test_rt_cannot_save_head_of_family_without_religion_and_occupation(): void
    {
        [$staff, , $household] = $this->createRtContext();

        $this->actingAs($staff)
            ->post(route('rt.residents.store'), [
                'household_id' => $household->id,
                'name' => 'Kepala Tanpa Agama',
                'is_head_of_family' => '1',
            ])
            ->assertSessionHasErrors(['religion', 'occupation']);

        $this->actingAs($staff)
            ->post(route('rt.residents.store'), [
                'household_id' => $household->id,
                'name' => 'Kepala Lengkap',
                'is_head_of_family' => '1',
                'religion' => 'Kristen',
                'occupation' => 'Karyawan swasta',
            ])
            ->assertRedirect(route('rt.residents.show', [
                'resident' => Resident::where('name', 'Kepala Lengkap')->value('id'),
                'filter' => 'aktif',
                'household' => $household->id,
            ]))
            ->assertSessionHasNoErrors();
    }

    public function test_rt_resident_form_renders_occupation_select(): void
    {
        [$staff] = $this->createRtContext();

        $this->actingAs($staff)
            ->get(route('rt.residents.create'))
            ->assertOk()
            ->assertSee('id="occupation"', false)
            ->assertSee('Petani/Pekebun')
            ->assertSee('Karyawan swasta');
    }

    public function test_kelurahan_resident_list_includes_legacy_incomplete_data(): void
    {
        [, , $household, $kelurahan] = $this->createRtContext();

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010088',
            'name' => 'KK Legacy',
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.population.index'))
            ->assertOk()
            ->assertSee('KK Legacy')
            ->assertSee('No. KK', false);
    }

    public function test_non_head_resident_does_not_require_religion(): void
    {
        [$staff, , $household] = $this->createRtContext();

        $this->actingAs($staff)
            ->post(route('rt.residents.store'), [
                'household_id' => $household->id,
                'name' => 'Anak KK',
            ])
            ->assertRedirect(route('rt.residents.show', [
                'resident' => Resident::where('name', 'Anak KK')->value('id'),
                'filter' => 'aktif',
                'household' => $household->id,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('residents', [
            'household_id' => $household->id,
            'name' => 'Anak KK',
            'domicile_status' => DomicileStatus::Aktif->value,
        ]);
    }

    public function test_rt_can_add_member_to_existing_household_with_head(): void
    {
        [$staff, , $household] = $this->createRtContext();

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Kepala Existing',
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
            'religion' => 'Islam',
            'occupation' => 'Petani/Pekebun',
        ]);

        $this->actingAs($staff)
            ->post(route('rt.residents.store'), [
                'household_id' => $household->id,
                'filter' => 'aktif',
                'name' => 'Anak Baru',
                'relationship_to_head' => 'Anak',
                'nik' => '3201010101010011',
            ])
            ->assertRedirect(route('rt.residents.show', [
                'resident' => Resident::where('name', 'Anak Baru')->value('id'),
                'filter' => 'aktif',
                'household' => $household->id,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('residents', [
            'household_id' => $household->id,
            'name' => 'Anak Baru',
            'relationship_to_head' => 'Anak',
            'domicile_status' => DomicileStatus::Aktif->value,
        ]);

        $this->assertNotNull(Resident::where('name', 'Anak Baru')->value('verified_at'));
    }

    public function test_rt_cannot_add_resident_with_duplicate_nik(): void
    {
        [$staff, , $household] = $this->createRtContext();

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Warga Existing',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $this->actingAs($staff)
            ->post(route('rt.residents.store'), [
                'household_id' => $household->id,
                'name' => 'Duplikat NIK',
                'nik' => '3201010101010099',
            ])
            ->assertSessionHasErrors(['nik']);
    }

    public function test_rt_cannot_add_second_head_of_family(): void
    {
        [$staff, , $household] = $this->createRtContext();

        Resident::create([
            'household_id' => $household->id,
            'name' => 'Kepala Pertama',
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
            'religion' => 'Islam',
            'occupation' => 'Petani/Pekebun',
        ]);

        $this->actingAs($staff)
            ->post(route('rt.residents.store'), [
                'household_id' => $household->id,
                'name' => 'Kepala Kedua',
                'is_head_of_family' => '1',
                'religion' => 'Kristen',
                'occupation' => 'Karyawan swasta',
            ])
            ->assertSessionHasErrors(['is_head_of_family']);
    }
}
