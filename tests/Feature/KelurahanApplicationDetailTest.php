<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\GeneratedLetter;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KelurahanApplicationDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: RtProfile, 1: User, 2: Application} */
    private function createPublishedLetterApplication(): array
    {
        $profile = RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 001',
            'email' => 'ketua001-detail@test.local',
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
            'family_card_number' => '3201010101010001',
            'address' => 'Jl. Test',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010001',
            'name' => 'Warga Surat',
            'phone' => '081234567801',
            'gender' => 'Laki-laki',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-01',
            'religion' => 'Islam',
            'occupation' => 'Karyawan',
            'education' => 'SMA',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $application = Application::create([
            'application_number' => 'RT001-2026050004',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        Storage::fake('local');
        $path = 'letters/test-surat.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 test');

        GeneratedLetter::create([
            'application_id' => $application->id,
            'file_path' => $path,
            'letter_number' => 'RT001/06/2026/0001',
            'issued_at' => now(),
            'signed_by' => $staff->id,
            'signed_at' => now(),
        ]);

        return [$profile, $staff, $application];
    }

    /** @return array{0: RtProfile, 1: Application} */
    private function createManualLetterApplication(): array
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $service = ServiceType::create([
            'code' => 'surat_manual_kel',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010008',
            'address' => 'Jl. Test Manual',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010008',
            'name' => 'Warga Manual',
            'phone' => '081234567808',
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $letterNumber = 'RT008/SK/06/2026/008';

        $application = Application::create([
            'application_number' => 'RT008-2026060008',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::SiapDiambil,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
            'completed_at' => now(),
            'form_data' => [
                'manual_letter' => [
                    'number' => $letterNumber,
                    'issued_at' => now()->toIso8601String(),
                    'issued_by' => 1,
                ],
            ],
        ]);

        return [$profile, $application];
    }

    private function createKelurahanUser(): User
    {
        return User::create([
            'name' => 'Petugas Kelurahan',
            'email' => 'kelurahan-detail@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);
    }

    private function createSuperAdmin(): User
    {
        return User::create([
            'name' => 'Admin Sistem',
            'email' => 'super-admin-detail@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
        ]);
    }

    public function test_kelurahan_application_index_shows_manual_letter_without_print_link(): void
    {
        [, $application] = $this->createManualLetterApplication();
        $kelurahan = $this->createKelurahanUser();
        $letterNumber = $application->manualLetterNumber();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.index'))
            ->assertOk()
            ->assertSee('Sudah diterbitkan', false)
            ->assertSee($letterNumber, false)
            ->assertSee(route('kelurahan.applications.show', $application, false), false)
            ->assertDontSee(route('kelurahan.applications.letter.print', $application, false), false);
    }

    public function test_kelurahan_application_show_displays_manual_letter_detail(): void
    {
        [, $application] = $this->createManualLetterApplication();
        $kelurahan = $this->createKelurahanUser();
        $letterNumber = $application->manualLetterNumber();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.show', $application))
            ->assertOk()
            ->assertSee('lw-kel-app-summary', false)
            ->assertSee('Surat RT', false)
            ->assertSee($letterNumber, false)
            ->assertSee('lw-kel-letter-card--issued', false)
            ->assertSee('tidak ada PDF di portal', false)
            ->assertDontSee('Lihat / cetak PDF', false)
            ->assertDontSee('belum diterbitkan RT', false);
    }

    public function test_kelurahan_filter_by_rt_profile_includes_rt_profile_id_assignment(): void
    {
        [$profile, $application] = $this->createManualLetterApplication();
        $kelurahan = $this->createKelurahanUser();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.index', ['rt_profile_id' => $profile->id]))
            ->assertOk()
            ->assertSee($application->application_number, false);

        $otherProfile = RtProfile::create([
            'rt_number' => '099',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 099',
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.index', ['rt_profile_id' => $otherProfile->id]))
            ->assertOk()
            ->assertDontSee($application->application_number, false);
    }

    public function test_kelurahan_application_index_shows_detail_and_letter_links(): void
    {
        [, , $application] = $this->createPublishedLetterApplication();
        $kelurahan = $this->createKelurahanUser();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.index'))
            ->assertOk()
            ->assertSee('Detail', false)
            ->assertSee('Lihat surat', false)
            ->assertSee(route('kelurahan.applications.show', $application, false), false)
            ->assertSee(route('kelurahan.applications.letter.print', $application, false), false);
    }

    public function test_kelurahan_application_show_displays_full_detail_sections(): void
    {
        [, $staff, $application] = $this->createPublishedLetterApplication();
        $kelurahan = $this->createKelurahanUser();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.show', $application))
            ->assertOk()
            ->assertSee('Detail permohonan', false)
            ->assertSee('Data pemohon', false)
            ->assertSee('Lampiran berkas', false)
            ->assertSee('Surat pengantar RT', false)
            ->assertSee('lw-kel-app-summary', false)
            ->assertSee('HP/WA', false)
            ->assertSee('Alamat tempat tinggal', false)
            ->assertSee('Pekerjaan', false)
            ->assertSee('Agama', false)
            ->assertSee('Status perkawinan', false)
            ->assertSee('Kewarganegaraan', false)
            ->assertSee('Karyawan', false)
            ->assertSee('Islam', false)
            ->assertSee('Kawin', false)
            ->assertSee('WNI', false)
            ->assertSee('Warga Surat', false)
            ->assertSee('081234567801', false)
            ->assertSee('RT001/06/2026/0001', false)
            ->assertSee('Penandatangan', false)
            ->assertSee($staff->name, false)
            ->assertSee('Lihat / cetak PDF', false)
            ->assertSee(route('kelurahan.applications.letter.print', $application, false), false)
            ->assertDontSee('Terima — lanjut susun surat', false)
            ->assertDontSee('Tolak permohonan', false);
    }

    public function test_kelurahan_application_show_displays_letter_metadata_and_print_link(): void
    {
        [, $staff, $application] = $this->createPublishedLetterApplication();
        $kelurahan = $this->createKelurahanUser();

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.show', $application))
            ->assertOk()
            ->assertSee('Surat pengantar RT', false)
            ->assertSee('RT001/06/2026/0001', false)
            ->assertSee('Penandatangan', false)
            ->assertSee($staff->name, false)
            ->assertSee('Lihat / cetak PDF', false)
            ->assertSee(route('kelurahan.applications.letter.print', $application, false), false);
    }

    public function test_super_admin_can_view_kelurahan_application_detail(): void
    {
        [, $staff, $application] = $this->createPublishedLetterApplication();
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)
            ->get(route('kelurahan.applications.show', $application))
            ->assertOk()
            ->assertSee($staff->name, false)
            ->assertSee(route('kelurahan.applications.letter.print', $application, false), false);
    }
}
