<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Household;
use App\Models\LetterTemplate;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\SuratPengantarTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
            'status' => ApplicationStatus::Diajukan,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        return [$staff, $application];
    }

    public function test_show_page_displays_manual_issue_form(): void
    {
        [$staff, $application] = $this->createIssuableApplication();

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertSee('lw-letter-issue-card', false)
            ->assertSee('Catat nomor surat &amp; kirim notifikasi', false)
            ->assertSee(route('rt.applications.letter.issue', $application), false)
            ->assertDontSee('Susun &amp; terbitkan surat', false)
            ->assertDontSee(route('rt.applications.letter.compose', $application), false);
    }

    public function test_show_page_displays_issued_status_after_manual_issue(): void
    {
        [$staff, $application] = $this->createIssuableApplication();
        $letterNumber = 'RT008/SK/06/2026/099';

        $application->update([
            'status' => ApplicationStatus::SiapDiambil,
            'completed_at' => now(),
            'form_data' => [
                'manual_letter' => [
                    'number' => $letterNumber,
                    'issued_at' => now()->toIso8601String(),
                    'issued_by' => $staff->id,
                ],
            ],
        ]);

        $this->actingAs($staff)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertSee('lw-letter-issue-status', false)
            ->assertSee($letterNumber, false)
            ->assertSee('mengambil surat fisik di sekretariat RT', false)
            ->assertDontSee('Catat nomor surat &amp; kirim notifikasi', false);
    }
}
