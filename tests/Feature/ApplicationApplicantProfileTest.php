<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\ResidentProfileDisplay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicationApplicantProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Application, 2: Resident} */
    private function createApplicationWithResident(): array
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 008',
            'email' => 'ketua008-profile@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010008',
            'address' => 'Jl. Profil Lengkap',
            'house_number' => '12',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010008',
            'name' => 'Warga Profil Lengkap',
            'phone' => '081234567808',
            'gender' => 'Perempuan',
            'birth_place' => 'Timika',
            'birth_date' => '1992-05-15',
            'religion' => 'Kristen',
            'occupation' => 'Guru',
            'education' => 'S1',
            'marital_status' => 'Belum Kawin',
            'citizenship' => 'WNI',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $application = Application::create([
            'application_number' => 'RT008-2026060099',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::Diajukan,
            'purpose' => 'Keperluan tes profil',
            'submitted_at' => now(),
        ]);

        return [$staff, $application, $resident];
    }

    public function test_resident_profile_display_from_resident_includes_standard_fields(): void
    {
        [, , $resident] = $this->createApplicationWithResident();

        $fields = ResidentProfileDisplay::fromResident($resident);

        $this->assertSame('Warga Profil Lengkap', $fields['nama']);
        $this->assertSame('3201010101010008', $fields['nik']);
        $this->assertStringContainsString('Timika', $fields['ttl']);
        $this->assertSame('Guru', $fields['pekerjaan']);
        $this->assertSame('Kristen', $fields['agama']);
        $this->assertSame('Belum Kawin', $fields['status_perkawinan']);
        $this->assertSame('WNI', $fields['kewarganegaraan']);
        $this->assertStringContainsString('Jl. Profil Lengkap', $fields['alamat']);
    }

    public function test_build_applicant_snapshot_includes_demographics(): void
    {
        [, , $resident] = $this->createApplicationWithResident();

        $snapshot = Application::buildApplicantSnapshot($resident);

        $this->assertSame('Guru', $snapshot['occupation']);
        $this->assertSame('Kristen', $snapshot['religion']);
        $this->assertSame('Belum Kawin', $snapshot['marital_status']);
        $this->assertSame('WNI', $snapshot['citizenship']);
    }

    public function test_rt_application_show_displays_full_applicant_profile(): void
    {
        [$staff, $application] = $this->createApplicationWithResident();

        $this->withoutVite();

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertSee('Warga Profil Lengkap', false)
            ->assertSee('3201010101010008', false)
            ->assertSee('Timika', false)
            ->assertSee('Guru', false)
            ->assertSee('Kristen', false)
            ->assertSee('Belum Kawin', false)
            ->assertSee('WNI', false)
            ->assertSee('Jl. Profil Lengkap', false);
    }

    public function test_kelurahan_application_show_displays_full_applicant_profile(): void
    {
        [, $application] = $this->createApplicationWithResident();

        $kelurahan = User::create([
            'name' => 'Petugas Kelurahan',
            'email' => 'kelurahan-profile@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.show', $application))
            ->assertOk()
            ->assertSee('Pekerjaan', false)
            ->assertSee('Agama', false)
            ->assertSee('Status perkawinan', false)
            ->assertSee('Kewarganegaraan', false)
            ->assertSee('Guru', false)
            ->assertSee('Kristen', false)
            ->assertSee('Belum Kawin', false);
    }

    public function test_archived_applicant_snapshot_shows_demographics_on_detail(): void
    {
        [$staff, $application, $resident] = $this->createApplicationWithResident();

        $application->archiveApplicantFromResident($resident);
        $application->refresh();

        $this->assertNull($application->resident_id);
        $this->assertSame('Guru', $application->applicantSnapshot()['occupation']);

        $fields = ResidentProfileDisplay::fromApplication($application);
        $this->assertSame('Guru', $fields['pekerjaan']);
        $this->assertSame('Kristen', $fields['agama']);

        $this->withoutVite();

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertSee('Data warga dihapus', false)
            ->assertSee('Guru', false)
            ->assertSee('Kristen', false)
            ->assertSee('Belum Kawin', false)
            ->assertSee('WNI', false);
    }
}
