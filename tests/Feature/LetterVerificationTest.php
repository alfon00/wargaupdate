<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\GeneratedLetter;
use App\Models\Household;
use App\Models\LetterTemplate;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\LetterVerificationLink;
use App\Support\SuratPengantarTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LetterVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Application} */
    private function createPublishedLetterApplication(): array
    {
        Storage::fake('local');

        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 008',
            'email' => 'ketua008@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        LetterTemplate::create([
            'service_type_id' => $service->id,
            'name' => 'Template Domisili',
            'body_html' => SuratPengantarTemplate::bodyHtml(),
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
            'nik' => '3201010101010008',
            'name' => 'Warga Surat',
            'phone' => '081234567808',
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
            'application_number' => 'RT008-2026050001',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        $this->actingAs($staff)
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => [
                    'nomor_surat' => 'RT008/SK/06/2026/0001',
                    'nama' => 'Warga Surat',
                    'nik' => '3201010101010008',
                    'ttl' => 'Timika, 1 Januari 1990',
                    'jenis_kelamin' => 'Laki-laki',
                    'pekerjaan' => 'Karyawan',
                    'no_ktp_kk' => '3201010101010008',
                    'kewarganegaraan' => 'WNI',
                    'pendidikan' => 'SMA',
                    'agama' => 'Islam',
                    'status_perkawinan' => 'Kawin',
                    'alamat' => 'Jl. Test No. 1',
                    'rt_rw' => 'RT 008 / RW 005',
                    'keperluan' => 'Keperluan administrasi',
                ],
            ])
            ->assertRedirect();

        $application->refresh();

        return [$staff, $application];
    }

    public function test_verification_page_shows_authentic_letter_without_letter_number(): void
    {
        [, $application] = $this->createPublishedLetterApplication();

        $letter = $application->generatedLetter;
        $this->assertNotNull($letter?->verification_token);

        $url = LetterVerificationLink::url($application);
        $this->assertNotNull($url);

        $this->get($url)
            ->assertOk()
            ->assertSee('Surat dinyatakan asli', false)
            ->assertSee('Rincian surat', false)
            ->assertSee('Surat Domisili', false)
            ->assertSee('Warga Surat', false)
            ->assertDontSee('Terverifikasi', false)
            ->assertDontSee('Verifikasi keaslian', false)
            ->assertDontSee('RT008/SK/06/2026/0001', false);
    }

    public function test_invalid_verification_token_returns_not_found(): void
    {
        $this->get(route('public.letter.verify', ['token' => 'token-tidak-valid']))
            ->assertNotFound();
    }

    public function test_unissued_letter_token_is_not_verifiable(): void
    {
        $service = ServiceType::create([
            'code' => 'surat_test',
            'name' => 'Surat Test',
            'is_active' => true,
        ]);

        $template = LetterTemplate::create([
            'service_type_id' => $service->id,
            'name' => 'Template Test',
            'body_html' => SuratPengantarTemplate::bodyHtml(),
            'is_active' => true,
        ]);

        $application = Application::create([
            'application_number' => 'RT008-2026050099',
            'service_type_id' => $service->id,
            'letter_verification_token' => 'draft-token-only',
            'status' => ApplicationStatus::VerifikasiRt,
            'submitted_at' => now(),
        ]);

        GeneratedLetter::create([
            'application_id' => $application->id,
            'letter_template_id' => $template->id,
            'file_path' => 'letters/test.pdf',
            'letter_number' => 'RT008/TEST/001',
            'verification_token' => 'draft-token-only',
            'issued_at' => null,
        ]);

        $this->get(route('public.letter.verify', ['token' => 'draft-token-only']))
            ->assertNotFound();
    }

    public function test_preview_and_publish_use_same_verification_token(): void
    {
        Storage::fake('local');

        $profile = RtProfile::create([
            'rt_number' => '008',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 008',
            'email' => 'ketua008b@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $service = ServiceType::create([
            'code' => 'surat_domisili_b',
            'name' => 'Surat Domisili B',
            'is_active' => true,
        ]);

        LetterTemplate::create([
            'service_type_id' => $service->id,
            'name' => 'Template',
            'body_html' => SuratPengantarTemplate::bodyHtml(),
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
            'nik' => '3201010101010009',
            'name' => 'Warga Dua',
            'gender' => 'Perempuan',
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
            'application_number' => 'RT008-2026050002',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'submitted_at' => now(),
        ]);

        $fields = [
            'nomor_surat' => 'RT008/SK/06/2026/0002',
            'nama' => 'Warga Dua',
            'nik' => '3201010101010009',
            'ttl' => 'Timika, 1 Januari 1990',
            'jenis_kelamin' => 'Perempuan',
            'pekerjaan' => 'Karyawan',
            'no_ktp_kk' => '3201010101010009',
            'kewarganegaraan' => 'WNI',
            'pendidikan' => 'SMA',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'alamat' => 'Jl. Test',
            'rt_rw' => 'RT 008',
            'keperluan' => 'Test',
        ];

        $this->actingAs($staff)
            ->post(route('rt.applications.letter.preview', $application), ['fields' => $fields])
            ->assertOk();

        $application->refresh();
        $previewToken = $application->letter_verification_token;
        $this->assertNotNull($previewToken);

        $this->actingAs($staff)
            ->post(route('rt.applications.letter.publish', $application), ['fields' => $fields])
            ->assertRedirect();

        $application->refresh();
        $this->assertSame($previewToken, $application->letter_verification_token);
        $this->assertSame($previewToken, $application->generatedLetter?->verification_token);

        $verifyUrl = LetterVerificationLink::url($application);
        $this->assertNotNull($verifyUrl);
        $this->assertStringContainsString('/surat/verifikasi/', $verifyUrl);
        $this->get($verifyUrl)->assertOk();
    }
}
