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

class LetterKopTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_uses_inauga_kop_and_new_surat_format(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '003',
            'rw_number' => '004',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 003',
            'alamat_kantor' => 'Jl. Merpati Desa Inauga',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 003',
            'email' => 'ketua003-kop@test.local',
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
            'pendataan_category' => '',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010003',
            'name' => 'Warga Kop',
            'domicile_status' => DomicileStatus::Aktif,
            'gender' => 'Laki-laki',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-01',
            'religion' => 'Islam',
            'occupation' => 'Wiraswasta',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
        ]);

        $application = Application::create([
            'application_number' => 'RT003-2026060001',
            'service_type_id' => $service->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $profile->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Administrasi sekolah',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($staff)
            ->post(route('rt.applications.letter.preview', $application), [
                'fields' => [
                    'keperluan' => 'Administrasi sekolah',
                ],
            ]);

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('PEMERINTAH', $html);
        $this->assertStringContainsString('MIMIKA', $html);
        $this->assertStringContainsString('DISTRIK WANIA', $html);
        $this->assertStringContainsString('KELURAHAN INAUGA', $html);
        $this->assertStringContainsString('SURAT PENGANTAR RUKUN TETANGGA RT 003', $html);
        $this->assertStringContainsString('N o m o r', $html);
        $this->assertStringContainsString('Times New Roman', $html);
        $this->assertStringContainsString('RT003/', $html);
        $this->assertStringContainsString('3201010101010003', $html);
        $this->assertStringContainsString('Hormat kami,', $html);
        $this->assertStringContainsString('Pengurus RT 003', $html);
        $this->assertStringContainsString('Administrasi sekolah', $html);
        $this->assertStringContainsString('Warga Kop', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringNotContainsString('DESA INAUGA', $html);
        $this->assertStringNotContainsString('Benar warga kami', $html);
        $this->assertStringNotContainsString('Mengetahui', $html);
    }
}
