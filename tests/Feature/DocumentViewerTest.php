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

class DocumentViewerTest extends TestCase
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
            'email' => 'ketua001-viewer@test.local',
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
        ]);

        return [$profile, $staff, $application];
    }

    public function test_rt_staff_can_open_letter_print_viewer(): void
    {
        [, $staff, $application] = $this->createPublishedLetterApplication();

        $response = $this->actingAs($staff)
            ->get(route('rt.applications.letter.print', $application));

        $response->assertOk();
        $response->assertSee('lw-doc-viewer-frame', false);
        $response->assertSee(route('rt.applications.letter.view', $application, false), false);
        $response->assertSee('Surat RT001-2026050004', false);
    }

    public function test_other_rt_staff_cannot_open_letter_print_viewer(): void
    {
        [, , $application] = $this->createPublishedLetterApplication();

        $otherProfile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $otherStaff = User::create([
            'name' => 'Ketua RT 008',
            'email' => 'ketua008-viewer@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $otherProfile->id,
        ]);

        $this->actingAs($otherStaff)
            ->get(route('rt.applications.letter.print', $application))
            ->assertNotFound();
    }

    public function test_letter_print_viewer_returns_404_without_published_letter(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 001',
            'email' => 'ketua001-empty@test.local',
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
            'family_card_number' => '3201010101010002',
            'address' => 'Jl. Test',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010002',
            'name' => 'Warga Tanpa Surat',
            'phone' => '081234567802',
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
            'application_number' => 'RT001-2026050099',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('rt.applications.letter.print', $application))
            ->assertNotFound();
    }

    public function test_kelurahan_letter_print_returns_404_for_manual_letter(): void
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
            'code' => 'surat_manual_viewer',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Test',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Warga Manual Viewer',
            'phone' => '081234567899',
            'is_head_of_family' => true,
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $application = Application::create([
            'application_number' => 'RT008-2026060099',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::SiapDiambil,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
            'completed_at' => now(),
            'form_data' => [
                'manual_letter' => [
                    'number' => 'RT008/SK/06/2026/099',
                    'issued_at' => now()->toIso8601String(),
                    'issued_by' => 1,
                ],
            ],
        ]);

        $kelurahan = User::create([
            'name' => 'Staff Kelurahan',
            'email' => 'kelurahan-manual-viewer@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.letter.print', $application))
            ->assertNotFound();
    }

    public function test_kelurahan_can_open_letter_print_viewer(): void
    {
        [, , $application] = $this->createPublishedLetterApplication();

        $kelurahan = User::create([
            'name' => 'Staff Kelurahan',
            'email' => 'kelurahan-viewer@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kelurahan,
        ]);

        $this->actingAs($kelurahan)
            ->get(route('kelurahan.applications.letter.print', $application))
            ->assertOk()
            ->assertSee('lw-doc-viewer-frame', false)
            ->assertSee(route('kelurahan.applications.letter.view', $application, false), false);
    }

    public function test_view_letter_returns_inline_content_disposition(): void
    {
        [, $staff, $application] = $this->createPublishedLetterApplication();

        $response = $this->actingAs($staff)
            ->get(route('rt.applications.letter.view', $application));

        $response->assertOk();
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
    }
}
