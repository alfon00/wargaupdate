<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\CitizenReport;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsSuratApplyPayload;
use Tests\TestCase;

class LayananRtRoutingTest extends TestCase
{
    use BuildsSuratApplyPayload;
    use RefreshDatabase;

    private function createRtWithStaff(string $rtNumber = '001'): array
    {
        $profile = RtProfile::create([
            'rt_number' => $rtNumber,
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT '.$rtNumber,
        ]);

        $staff = User::create([
            'name' => 'Ketua RT '.$rtNumber,
            'email' => 'ketua-rt-'.$rtNumber.'@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        return [$profile, $staff];
    }

    private function identityPayload(
        int $rtProfileId,
        string $nik = '3201010101010001',
        string $purpose = 'Keperluan uji',
    ): array {
        return $this->applyStorePayload($rtProfileId, $nik, $purpose);
    }

    private function createActiveResident(RtProfile $rt, string $nik = '3201010101010001'): Resident
    {
        $household = Household::create([
            'rt_profile_id' => $rt->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Uji No. 1',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        return Resident::create([
            'household_id' => $household->id,
            'nik' => $nik,
            'name' => 'Warga Uji',
            'phone' => '081234567890',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-01',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'occupation' => 'Karyawan',
            'education' => 'SMA',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'domicile_status' => DomicileStatus::Aktif,
        ]);
    }

    /** @return array<string, mixed> */
    private function pendataanUlangPayload(Resident $head): array
    {
        return [
            'whatsapp_notify' => '1',
            'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
            'members' => [[
                'resident_id' => $head->id,
                'document_id' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            ]],
        ];
    }

    public function test_surat_application_appears_on_rt_panel(): void
    {
        [$rtA, $staffA] = $this->createRtWithStaff('001');
        $resident = $this->createActiveResident($rtA);
        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        $response = $this->withSession(['surat_resident_id' => $resident->id])
            ->post(route('services.apply.store', $service), $this->identityPayload($rtA->id));

        $response->assertRedirect();
        $application = Application::first();
        $this->assertNotNull($application);
        $this->assertSame($rtA->id, $application->rt_profile_id);

        $this->actingAs($staffA)
            ->get(route('rt.applications.index'))
            ->assertOk()
            ->assertSee($application->application_number);

        $this->actingAs($staffA)
            ->get(route('rt.applications.show', $application))
            ->assertOk()
            ->assertSee('Data pemohon', false)
            ->assertSee('Warga Uji', false)
            ->assertDontSee('Orang yang diajukan surat', false);
    }

    public function test_surat_rejects_nik_registered_in_other_rt(): void
    {
        [$rtA] = $this->createRtWithStaff('001');
        [$rtB] = $this->createRtWithStaff('002');

        $this->createActiveResident($rtB, '3201010101010099');

        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        $residentB = Resident::where('nik', '3201010101010099')->first();
        $this->assertNotNull($residentB);

        $this->withSession(['surat_resident_id' => $residentB->id])
            ->from(route('services.apply', $service))
            ->post(route('services.apply.store', $service), $this->identityPayload($rtA->id, '3201010101010099', 'Uji salah RT'))
            ->assertSessionHasErrors('rt_profile_id');
    }

    public function test_pendataan_ulang_appears_on_rt_verification_queue(): void
    {
        [$rtA, $staffA] = $this->createRtWithStaff('003');
        $head = $this->createActiveResident($rtA, '3201010101010033');
        $head->update(['name' => 'Kepala Ulang']);

        $this->post(route('services.pendataan-ulang.verify'), [
            'rt_profile_id' => $rtA->id,
            'nik' => $head->nik,
            'phone' => $head->phone,
        ])->assertRedirect(route('services.pendataan-ulang'));

        $this->withSession(['pendataan_ulang_resident_id' => $head->id])
            ->post(route('services.pendataan-ulang.store'), $this->pendataanUlangPayload($head))
            ->assertRedirect(route('services.pendataan-ulang.success'));

        $head->refresh();
        $this->assertSame(DomicileStatus::MenungguVerifikasi, $head->domicile_status);

        $this->actingAs($staffA)
            ->get(route('rt.pendataan.index'))
            ->assertOk()
            ->assertSee('Kepala Ulang');
    }

    public function test_pengaduan_via_kontak_appears_on_rt_reports(): void
    {
        [$rtA, $staffA] = $this->createRtWithStaff('004');

        $this->post(route('contact.store'), [
            'rt_profile_id' => $rtA->id,
            'category' => 'pengaduan_lingkungan',
            'reporter_name' => 'Pelapor Uji',
            'phone' => '081200000004',
            'incident_type' => 'sampah',
            'incident_location' => 'Depan pos RT',
            'message' => 'Perlu segera dibersihkan di area kompleks.',
            'declaration' => '1',
        ])->assertRedirect(route('contact.success'));

        $report = CitizenReport::first();
        $this->assertNotNull($report);
        $this->assertSame($rtA->id, $report->rt_profile_id);
        $this->assertStringContainsString('Pengaduan lingkungan', $report->subject);

        $this->actingAs($staffA)
            ->get(route('rt.reports.index'))
            ->assertOk()
            ->assertSee($report->report_number);

        $this->actingAs($staffA)
            ->get(route('rt.dashboard'))
            ->assertOk()
            ->assertSee($report->report_number);
    }

    public function test_application_scope_matches_normalized_rt_number(): void
    {
        [$rtA, $staffA] = $this->createRtWithStaff('8');

        $duplicate = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua Duplikat',
        ]);

        $household = Household::create([
            'rt_profile_id' => $duplicate->id,
            'address' => 'Alamat',
            'status' => 'aktif',
        ]);
        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010088',
            'name' => 'Warga Normalisasi',
            'phone' => '081200000088',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        Application::create([
            'application_number' => Application::generateNumber('008'),
            'service_type_id' => ServiceType::create([
                'code' => 'surat_umum',
                'name' => 'Surat Umum',
                'is_active' => true,
            ])->id,
            'resident_id' => $resident->id,
            'rt_profile_id' => $duplicate->id,
            'status' => ApplicationStatus::Diajukan,
            'purpose' => 'Uji normalisasi',
            'submitted_at' => now(),
        ]);

        $this->actingAs($staffA)
            ->get(route('rt.applications.index'))
            ->assertOk()
            ->assertSee('RT008-');
    }
}
