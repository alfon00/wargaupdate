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
use App\Support\SuratPengantarTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RtApplicationShowLetterTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Application} */
    private function createIssuableApplication(string $applicationNumber = 'RT008-2026050002'): array
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 008',
            'ketua_rw' => 'Ketua RW 005',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 008',
            'email' => 'ketua008-show@test.local',
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
            'application_number' => $applicationNumber,
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        return [$staff, $application];
    }

    public function test_show_page_displays_digital_letter_card_with_compose_link(): void
    {
        [$staff, $application] = $this->createIssuableApplication();

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertSee('lw-letter-show-card', false)
            ->assertSee('surat belum diterbitkan', false)
            ->assertSee('Susun &amp; terbitkan surat', false)
            ->assertSee(route('rt.applications.letter.compose', $application), false)
            ->assertDontSee('gambar tanda tangan Ketua RT', false)
            ->assertDontSee('Catat nomor surat &amp; kirim notifikasi', false);
    }

    public function test_show_page_displays_issued_pdf_status_after_publish(): void
    {
        Storage::fake('local');
        [$staff, $application] = $this->createIssuableApplication();
        $letterNumber = 'RT008/06/2026/099';
        $pdfPath = 'letters/test-published.pdf';
        Storage::disk('local')->put($pdfPath, '%PDF-1.4 test');

        GeneratedLetter::create([
            'application_id' => $application->id,
            'letter_template_id' => LetterTemplate::first()->id,
            'file_path' => $pdfPath,
            'letter_number' => $letterNumber,
            'letter_fields' => [],
            'signature_path' => 'letters/test-signature.png',
            'signed_at' => now(),
            'issued_at' => now(),
        ]);

        $application->update([
            'status' => ApplicationStatus::SiapDiambil,
            'completed_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertSee('lw-letter-show-status', false)
            ->assertSee($letterNumber, false)
            ->assertSee('Lihat / cetak PDF', false)
            ->assertSee('Susun ulang / kirim WhatsApp', false)
            ->assertDontSee('Unduh PDF', false)
            ->assertDontSee('Catat nomor surat &amp; kirim notifikasi', false);
    }
}
