<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSuratApplyPayload;
use Tests\TestCase;

class LayananSuratFlowTest extends TestCase
{
    use BuildsSuratApplyPayload;
    use RefreshDatabase;

    private function createRtProfile(string $rtNumber = '001'): RtProfile
    {
        return RtProfile::create([
            'rt_number' => $rtNumber,
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT '.$rtNumber,
        ]);
    }

    private function createResident(
        RtProfile $rt,
        string $nik = '3201010101010001',
        DomicileStatus $status = DomicileStatus::Aktif,
        string $phone = '081234567890',
    ): Resident {
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
            'phone' => $phone,
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
            'domicile_status' => $status,
        ]);
    }

    /** @return array<string, mixed> */
    private function verifyPayload(int $rtProfileId, string $nik = '3201010101010001'): array
    {
        return [
            'rt_profile_id' => $rtProfileId,
            'nik' => $nik,
            'phone' => '081234567890',
        ];
    }

    private function createService(string $code = 'surat_domisili'): ServiceType
    {
        return ServiceType::create([
            'code' => $code,
            'name' => 'Surat Keterangan Domisili',
            'description' => 'Keterangan domisili untuk keperluan administrasi umum.',
            'is_active' => true,
        ]);
    }

    /** @return array<string, string> */
    private function intendedServiceSession(string $code = 'surat_domisili'): array
    {
        return ['surat_intended_service_code' => $code];
    }

    public function test_verify_rejects_unknown_nik(): void
    {
        $rt = $this->createRtProfile();
        $this->createService();

        $this->withSession($this->intendedServiceSession())
            ->from(route('services.surat.verify-form'))
            ->post(route('services.surat.verify'), $this->verifyPayload($rt->id))
            ->assertSessionHasErrors('nik')
            ->assertSessionMissing('surat_resident_id');
    }

    public function test_verify_rejects_invalid_phone_length(): void
    {
        $rt = $this->createRtProfile();
        $this->createResident($rt);
        $this->createService();

        $payload = $this->verifyPayload($rt->id);
        $payload['phone'] = '0812345678';

        $this->withSession($this->intendedServiceSession())
            ->from(route('services.surat.verify-form'))
            ->post(route('services.surat.verify'), $payload)
            ->assertSessionHasErrors('phone')
            ->assertSessionMissing('surat_resident_id');
    }

    public function test_verify_rejects_resident_awaiting_verification(): void
    {
        $rt = $this->createRtProfile();
        $this->createResident($rt, status: DomicileStatus::MenungguVerifikasi);
        $this->createService();

        $this->withSession($this->intendedServiceSession())
            ->from(route('services.surat.verify-form'))
            ->post(route('services.surat.verify'), $this->verifyPayload($rt->id))
            ->assertSessionHasErrors('nik')
            ->assertSessionMissing('surat_resident_id');
    }

    public function test_verify_succeeds_with_identity_only(): void
    {
        $rt = $this->createRtProfile();
        $resident = $this->createResident($rt);
        $service = $this->createService();

        $this->withSession($this->intendedServiceSession())
            ->from(route('services.surat.verify-form'))
            ->post(route('services.surat.verify'), $this->verifyPayload($rt->id))
            ->assertRedirect(route('services.apply', $service))
            ->assertSessionHas('surat_resident_id', $resident->id);
    }

    public function test_surat_index_shows_service_catalog(): void
    {
        $service = $this->createService();

        $this->get(route('services.surat'))
            ->assertOk()
            ->assertSee('Pilih jenis surat')
            ->assertSee('Lihat persyaratan', false)
            ->assertSee($service->catalogLabel(), false);
    }

    public function test_surat_verify_page_shows_identity_form(): void
    {
        $this->createRtProfile();
        $this->createService();

        $this->withSession($this->intendedServiceSession())
            ->get(route('services.surat.verify-form'))
            ->assertOk()
            ->assertSee('Lanjut ke formulir permohonan', false)
            ->assertDontSee('id="lw-face-capture"', false);
    }

    public function test_verify_success_redirects_to_apply_form(): void
    {
        $rt = $this->createRtProfile();
        $resident = $this->createResident($rt);
        $service = $this->createService();

        $this->withSession($this->intendedServiceSession())
            ->post(route('services.surat.verify'), $this->verifyPayload($rt->id))
            ->assertRedirect(route('services.apply', $service))
            ->assertSessionHas('surat_resident_id', $resident->id)
            ->assertSessionMissing('success');

        $response = $this->get(route('services.apply', $service));
        $response->assertOk()
            ->assertSee('Warga Uji')
            ->assertSee('Identitas terverifikasi', false)
            ->assertSee('unggah berkas KK serta KTP/KIA pemohon', false)
            ->assertDontSee('Identitas berhasil diverifikasi', false)
            ->assertDontSee('Persyaratan umum', false);

        $this->assertSame(1, substr_count($response->getContent(), 'class="lw-alert lw-alert--success'));
    }

    public function test_apply_requires_verification_session(): void
    {
        $rt = $this->createRtProfile();
        $service = $this->createService();

        $this->get(route('services.apply', $service))
            ->assertRedirect(route('services.surat.verify-form'));

        $this->post(route('services.apply.store', $service), $this->applyStorePayload($rt->id, '3201010101010001', 'Uji tanpa session'))
            ->assertRedirect(route('services.surat.verify-form'));

        $this->assertSame(0, Application::count());
    }

    public function test_apply_with_session_creates_application_without_new_resident(): void
    {
        $rt = $this->createRtProfile();
        $resident = $this->createResident($rt);
        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        $this->withSession([
            'surat_resident_id' => $resident->id,
        ])
            ->post(route('services.apply.store', $service), $this->applyStorePayload($rt->id))
            ->assertRedirect()
            ->assertSessionMissing('surat_resident_id');

        $this->assertSame(1, Resident::count());
        $application = Application::first();
        $this->assertNotNull($application);
        $this->assertSame($resident->id, $application->resident_id);
    }

    public function test_after_submit_requires_verification_for_next_application(): void
    {
        $rt = $this->createRtProfile();
        $resident = $this->createResident($rt);
        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        $this->withSession([
            'surat_resident_id' => $resident->id,
        ])
            ->post(route('services.apply.store', $service), $this->applyStorePayload($rt->id, '3201010101010001', 'Permohonan pertama'))
            ->assertRedirect()
            ->assertSessionMissing('surat_resident_id');

        $this->get(route('services.surat.catalog'))
            ->assertRedirect(route('services.surat'));

        $this->get(route('services.apply', $service))
            ->assertRedirect(route('services.surat.verify-form'));
    }

    public function test_success_page_shows_submitted_status(): void
    {
        $rt = $this->createRtProfile();
        $resident = $this->createResident($rt);
        $service = ServiceType::create([
            'code' => 'surat_umum',
            'name' => 'Surat Umum',
            'is_active' => true,
        ]);

        $this->withSession([
            'surat_resident_id' => $resident->id,
        ])
            ->from(route('services.apply', $service))
            ->post(route('services.apply.store', $service), $this->applyStorePayload($rt->id, '3201010101010001', 'Uji status'))
            ->assertRedirect();

        $application = Application::first();
        $this->assertNotNull($application);

        $this->get(route('services.apply.success', $application))
            ->assertOk()
            ->assertSee('menunggu verifikasi pengurus RT')
            ->assertSee('Diajukan')
            ->assertSee('Simpan nomor permohonan', false)
            ->assertSee('lacak status', false)
            ->assertSee('Salin nomor', false)
            ->assertSee($application->application_number, false)
            ->assertSee('id="application-number-value"', false)
            ->assertSee('data-copy-text="'.$application->application_number.'"', false);
    }

    public function test_apply_store_saves_letter_subjects_from_pemohon(): void
    {
        $rt = $this->createRtProfile();
        $resident = $this->createResident($rt);
        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Domisili',
            'required_fields' => ['KK', 'KTP'],
            'is_active' => true,
        ]);

        $this->withSession([
            'surat_resident_id' => $resident->id,
        ])
            ->post(route('services.apply.store', $service), $this->applyStorePayload($rt->id, $resident->nik))
            ->assertRedirect();

        $application = Application::first();
        $this->assertNotNull($application);
        $this->assertSame(1, $application->letterSubjectCount());
        $this->assertCount(1, $application->letterSubjects());
        $this->assertSame($resident->name, $application->letterSubjects()[0]['name']);
        $this->assertSame($resident->nik, $application->letterSubjects()[0]['nik']);
        $this->assertSame($resident->id, $application->letterSubjects()[0]['resident_id']);
        $this->assertSame(2, ApplicationDocument::where('application_id', $application->id)->count());
        $this->assertSame('req_0', ApplicationDocument::where('application_id', $application->id)->orderBy('id')->value('document_type'));
        $this->assertSame('req_1', ApplicationDocument::where('application_id', $application->id)->orderByDesc('id')->value('document_type'));
    }

    public function test_apply_store_requires_documents(): void
    {
        $rt = $this->createRtProfile();
        $resident = $this->createResident($rt);
        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Domisili',
            'required_fields' => ['KK', 'KTP'],
            'is_active' => true,
        ]);

        $this->withSession([
            'surat_resident_id' => $resident->id,
        ])
            ->from(route('services.apply', $service))
            ->post(route('services.apply.store', $service), $this->applyStorePayload($rt->id, $resident->nik, 'Tanpa berkas', withDocuments: false))
            ->assertSessionHasErrors('documents');

        $this->assertSame(0, Application::count());
    }

    public function test_apply_store_saves_documents_for_surat_usaha(): void
    {
        $rt = $this->createRtProfile();
        $resident = $this->createResident($rt);
        $service = ServiceType::create([
            'code' => 'surat_usaha',
            'name' => 'Surat Keterangan Usaha (SKU)',
            'required_fields' => ['KK', 'KTP'],
            'is_active' => true,
        ]);

        $payload = [
            ...$this->applyStorePayload($rt->id, $resident->nik, 'Usaha baru'),
            'nama_usaha' => 'Toko Uji',
            'jenis_usaha' => 'Retail',
            'alamat_usaha' => 'Jl. Usaha No. 1',
        ];

        $this->withSession([
            'surat_resident_id' => $resident->id,
        ])
            ->post(route('services.apply.store', $service), $payload)
            ->assertRedirect();

        $application = Application::first();
        $this->assertNotNull($application);
        $this->assertSame('surat_usaha', $application->serviceType->code);
        $this->assertSame(2, ApplicationDocument::where('application_id', $application->id)->count());
    }

    public function test_apply_store_always_enables_whatsapp_notify(): void
    {
        $rt = $this->createRtProfile();
        $resident = $this->createResident($rt);
        $resident->update(['whatsapp_notify' => false]);
        $service = ServiceType::create([
            'code' => 'surat_domisili',
            'name' => 'Surat Domisili',
            'is_active' => true,
        ]);

        $payload = $this->applyStorePayload($rt->id, $resident->nik);
        unset($payload['whatsapp_notify']);

        $this->withSession([
            'surat_resident_id' => $resident->id,
        ])
            ->post(route('services.apply.store', $service), $payload)
            ->assertRedirect();

        $this->assertTrue($resident->fresh()->whatsapp_notify);
    }
}
