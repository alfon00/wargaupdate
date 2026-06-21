<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Household;
use App\Models\GeneratedLetter;
use App\Models\LetterTemplate;
use App\Models\NotificationLog;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\LetterDownloadLink;
use App\Support\SuratPengantarTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LetterComposeTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: RtProfile, 1: User, 2: Application} */
    private function createLetterReadyApplication(): array
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
            'whatsapp_notify' => true,
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

        return [$profile, $staff, $application];
    }

    /** @return array<string, string> */
    private function sampleFields(?Application $application = null): array
    {
        return [
            'nomor_surat' => $application
                ? \App\Services\LetterGeneratorService::suggestLetterNumber($application)
                : 'RT008/06/2026/0001',
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
        ];
    }

    public function test_compose_letter_renders_compose_page(): void
    {
        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->withoutVite();

        $this->actingAs($staff)
            ->get(route('rt.applications.letter.compose', $application))
            ->assertOk()
            ->assertSee('Susun &amp; terbitkan surat', false)
            ->assertSee('letter-compose-root', false)
            ->assertSee('Nomor surat', false)
            ->assertSee('name="fields[nomor_surat]"', false)
            ->assertSee('Tanda tangan', false)
            ->assertSee('letter-signature-canvas', false);
    }

    public function test_publish_letter_uses_custom_nomor_surat(): void
    {
        Storage::fake('local');
        [, $staff, $application] = $this->createLetterReadyApplication();
        $customNumber = 'RT008/SK/06/2026/099';

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => array_merge($this->sampleFields($application), [
                    'nomor_surat' => $customNumber,
                ]),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application))
            ->assertSessionHas('success');

        $application->refresh();
        $this->assertSame($customNumber, $application->generatedLetter?->letter_number);
    }

    public function test_letter_resident_lookup_by_nik_returns_applicant_fields(): void
    {
        [$profile, $staff, $application] = $this->createLetterReadyApplication();

        $otherHousehold = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Lain',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
        ]);

        Resident::create([
            'household_id' => $otherHousehold->id,
            'nik' => '3201010101010099',
            'name' => 'Ani Keluarga',
            'phone' => '081234567899',
            'gender' => 'Perempuan',
            'birth_place' => 'Timika',
            'birth_date' => '1992-02-02',
            'religion' => 'Islam',
            'occupation' => 'Ibu Rumah Tangga',
            'education' => 'SMA',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'is_head_of_family' => false,
            'relationship_to_head' => 'Istri',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $this->actingAs($staff)
            ->getJson(route('rt.applications.letter.resident-lookup', $application).'?nik=3201010101010099')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('fields.nama', 'Ani Keluarga')
            ->assertJsonPath('fields.nik', '3201010101010099');
    }

    public function test_letter_resident_lookup_by_name_returns_choices_when_ambiguous(): void
    {
        [$profile, $staff, $application] = $this->createLetterReadyApplication();

        foreach ([
            ['nik' => '3201010101010101', 'name' => 'Budi Satu'],
            ['nik' => '3201010101010102', 'name' => 'Budi Dua'],
        ] as $index => $data) {
            $household = Household::create([
                'rt_profile_id' => $profile->id,
                'family_card_number' => '32010101010101'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'address' => 'Jl. Budi '.$index,
                'status' => 'aktif',
                'pendataan_category' => 'warga_baru',
            ]);

            Resident::create([
                'household_id' => $household->id,
                'nik' => $data['nik'],
                'name' => $data['name'],
                'phone' => '0812345678'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
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
        }

        $response = $this->actingAs($staff)
            ->getJson(route('rt.applications.letter.resident-lookup', $application).'?name=Budi');

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonCount(2, 'choices');
    }

    public function test_letter_resident_lookup_rejects_nik_from_other_rt(): void
    {
        [, $staff, $application] = $this->createLetterReadyApplication();

        $otherProfile = RtProfile::create([
            'rt_number' => '009',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 009',
        ]);

        $otherHousehold = Household::create([
            'rt_profile_id' => $otherProfile->id,
            'family_card_number' => '3201010101017777',
            'address' => 'Jl. RT Lain',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
        ]);

        Resident::create([
            'household_id' => $otherHousehold->id,
            'nik' => '3201010101017777',
            'name' => 'Warga RT Lain',
            'phone' => '081234567777',
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

        $this->actingAs($staff)
            ->getJson(route('rt.applications.letter.resident-lookup', $application).'?nik=3201010101017777')
            ->assertNotFound();
    }

    public function test_other_rt_staff_cannot_lookup_letter_resident(): void
    {
        [, , $application] = $this->createLetterReadyApplication();

        $otherProfile = RtProfile::create([
            'rt_number' => '010',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 010',
        ]);

        $otherStaff = User::create([
            'name' => 'Ketua RT 010',
            'email' => 'ketua010-lookup@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $otherProfile->id,
        ]);

        $this->actingAs($otherStaff)
            ->getJson(route('rt.applications.letter.resident-lookup', $application).'?nik=3201010101010008')
            ->assertNotFound();
    }

    public function test_publish_letter_generates_pdf_and_redirects_to_compose(): void
    {
        Storage::fake('local');
        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application))
            ->assertSessionHas('success');

        $application->refresh();
        $this->assertNotNull($application->generatedLetter);
        $this->assertSame(ApplicationStatus::SiapDiambil, $application->status);
    }

    public function test_save_letter_signature_persists_in_form_data(): void
    {
        [, $staff, $application] = $this->createLetterReadyApplication();
        $signature = $this->sampleSignatureDataUri();

        $this->actingAs($staff)
            ->postJson(route('rt.applications.letter.signature', $application), [
                'signature_data' => $signature,
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $application->refresh();
        $this->assertSame($signature, $application->form_data['letter']['signature_data'] ?? null);
    }

    public function test_draft_save_includes_signature(): void
    {
        [, $staff, $application] = $this->createLetterReadyApplication();
        $signature = $this->sampleSignatureDataUri();

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.draft', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $signature,
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application));

        $application->refresh();
        $this->assertSame($signature, $application->form_data['letter']['signature_data'] ?? null);
    }

    public function test_preview_fragment_excludes_inline_styles(): void
    {
        $html = '<!DOCTYPE html><html><head><style>body{margin:36px}</style></head><body><div class="kop">KOP</div></body></html>';

        $fragment = \App\Support\LetterPreviewHtml::extractFragment($html);

        $this->assertStringContainsString('class="kop"', $fragment);
        $this->assertStringNotContainsString('<style', $fragment);
    }

    public function test_preview_returns_surat_pengantar_html(): void
    {
        [, $staff, $application] = $this->createLetterReadyApplication();

        $response = $this->actingAs($staff)
            ->post(route('rt.applications.letter.preview', $application), [
                'fields' => $this->sampleFields(),
            ]);

        $response->assertOk();
        $this->assertStringContainsString('SURAT PENGANTAR RUKUN TETANGGA', $response->getContent());
        $this->assertStringContainsString('Warga Surat', $response->getContent());
    }

    public function test_preview_tab_response_includes_print_toolbar(): void
    {
        [, $staff, $application] = $this->createLetterReadyApplication();

        $response = $this->actingAs($staff)
            ->post(route('rt.applications.letter.preview', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ]);

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('lw-letter-tab-toolbar', $html);
        $this->assertStringContainsString('lw-letter-tab-print', $html);
        $this->assertStringContainsString('Cetak', $html);
        $this->assertStringContainsString('window.print()', $html);
        $this->assertStringContainsString('lw-letter-tab-back', $html);
        $this->assertStringContainsString('Kembali ke susun surat', $html);
        $this->assertStringContainsString(route('rt.applications.letter.compose', $application, false), $html);
        $this->assertStringContainsString('SURAT PENGANTAR RUKUN TETANGGA', $html);
        $this->assertStringContainsString('class="kop"', $html);
    }

    public function test_preview_ajax_response_is_raw_html_without_tab_toolbar(): void
    {
        [, $staff, $application] = $this->createLetterReadyApplication();

        $response = $this->actingAs($staff)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('rt.applications.letter.preview', $application), [
                'fields' => $this->sampleFields(),
            ]);

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringNotContainsString('lw-letter-tab-toolbar', $html);
        $this->assertStringNotContainsString('Kembali ke susun surat', $html);
        $this->assertStringContainsString('class="kop"', $html);
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
    }

    public function test_preview_works_with_partial_fields_for_usaha(): void
    {
        [, $staff, $application] = $this->createLetterReadyApplication();

        $service = ServiceType::where('code', 'surat_usaha')->first()
            ?? ServiceType::create([
                'code' => 'surat_usaha',
                'name' => 'Surat Usaha',
                'is_active' => true,
            ]);

        $application->update(['service_type_id' => $service->id]);
        \App\Models\LetterTemplate::updateOrCreate(
            ['service_type_id' => $service->id],
            [
                'name' => 'Template Usaha',
                'body_html' => \App\Support\SuratPengantarTemplate::bodyHtml(),
                'is_active' => true,
            ]
        );

        $response = $this->actingAs($staff)
            ->post(route('rt.applications.letter.preview', $application), [
                'fields' => [
                    'nama' => 'Warga Surat',
                ],
            ]);

        $response->assertOk();
        $this->assertStringContainsString('SURAT PENGANTAR RUKUN TETANGGA', $response->getContent());
    }

    public function test_publish_letter_requires_signature(): void
    {
        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => '',
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application))
            ->assertSessionHasErrors('signature_data');
    }

    public function test_compose_letter_renders_usaha_application(): void
    {
        [, $staff, $application] = $this->prepareUsahaApplication();

        $this->withoutVite();

        $this->actingAs($staff)
            ->get(route('rt.applications.letter.compose', $application))
            ->assertOk()
            ->assertSee('letter-compose-root', false);
    }

    public function test_publish_letter_generates_pdf_for_sku(): void
    {
        Storage::fake('local');
        [, $staff, $application] = $this->prepareUsahaApplication();

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => array_merge($this->sampleFields(), $this->sampleUsahaFields()),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application))
            ->assertSessionHas('success');

        $application->refresh();
        $this->assertNotNull($application->generatedLetter);
    }

    public function test_default_values_prefill_usaha_from_application_form_data(): void
    {
        [, , $application] = $this->prepareUsahaApplication();

        $application->update([
            'form_data' => [
                'letter' => [
                    'fields' => [
                        'nama_usaha' => 'Warung Sejahtera',
                        'jenis_usaha' => 'Perdagangan',
                        'alamat_usaha' => 'Jl. Usaha No. 5',
                    ],
                ],
            ],
        ]);

        $values = \App\Support\LetterFieldSchema::defaultValues($application->fresh());

        $this->assertSame('Warung Sejahtera', $values['nama_usaha']);
        $this->assertSame('Perdagangan', $values['jenis_usaha']);
        $this->assertSame('Jl. Usaha No. 5', $values['alamat_usaha']);
    }

    public function test_application_resolves_canonical_rt_profile_for_letter_preview(): void
    {
        $canonical = RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
            'alamat_kantor' => 'Sekretariat RT 001',
        ]);

        $duplicate = RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'slug' => 'rt-001-duplikat',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 001',
            'email' => 'ketua001-canonical@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $canonical->id,
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
            'rt_profile_id' => $duplicate->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Test',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Warga RT001',
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
            'rt_profile_id' => $duplicate->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        $application->refresh();
        $fields = \App\Support\LetterFieldSchema::defaultValues($application);

        $previewResponse = $this->actingAs($staff)
            ->post(route('rt.applications.letter.preview', $application), [
                'fields' => $fields,
            ]);

        $previewResponse->assertOk();
        $previewHtml = $previewResponse->getContent();
        $this->assertStringContainsString('KELURAHAN INAUGA', $previewHtml);
        $this->assertStringContainsString('DISTRIK WANIA', $previewHtml);
    }

    public function test_sync_letter_profiles_from_rt001_aligns_other_rt_kop_fields(): void
    {
        $baseline = RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'alamat_kantor' => 'Sekretariat RT 001, Kelurahan Inauga',
        ]);

        $target = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => null,
            'kota' => null,
            'provinsi' => null,
            'ketua_rt' => 'Ketua RT Delapan',
        ]);

        $this->artisan('lw:sync-letter-profiles-from-rt001', ['--apply' => true])
            ->assertSuccessful();

        $target->refresh();

        $this->assertSame('Kelurahan Inauga', $target->kelurahan);
        $this->assertSame('Distrik Wania', $target->kecamatan);
        $this->assertSame('Kabupaten Mimika', $target->kota);
        $this->assertSame('Papua Tengah', $target->provinsi);
        $this->assertStringContainsString('RT 008', (string) $target->alamat_kantor);
        $this->assertSame('Ketua RT Delapan', $target->ketua_rt);
        $this->assertNotSame($baseline->id, $target->id);
    }

    public function test_rt008_preview_uses_same_kop_wilayah_as_rt001_after_sync(): void
    {
        RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'alamat_kantor' => 'Sekretariat RT 001',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        $profile008 = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => null,
            'kota' => null,
            'provinsi' => null,
            'ketua_rt' => 'Ketua RT 008',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 008',
            'email' => 'ketua008-sync@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile008->id,
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
            'rt_profile_id' => $profile008->id,
            'family_card_number' => '3201010101010008',
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
            'rt_profile_id' => $profile008->id,
            'status' => ApplicationStatus::VerifikasiRt,
            'purpose' => 'Keperluan administrasi',
            'submitted_at' => now(),
        ]);

        $this->artisan('lw:sync-letter-profiles-from-rt001', ['--apply' => true])
            ->assertSuccessful();

        $response = $this->actingAs($staff)
            ->post(route('rt.applications.letter.preview', $application), [
                'fields' => $this->sampleFields(),
            ]);

        $response->assertOk();
        $this->assertStringContainsString('KELURAHAN INAUGA', $response->getContent());
        $this->assertStringContainsString('DISTRIK WANIA', $response->getContent());
        $this->assertStringContainsString('Ketua RT 008', $response->getContent());
    }

    public function test_preview_includes_signature_image_in_ttd_area(): void
    {
        [, $staff, $application] = $this->createLetterReadyApplication();

        $response = $this->actingAs($staff)
            ->post(route('rt.applications.letter.preview', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ]);

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('class="ttd-img"', $html);
        $this->assertMatchesRegularExpression('/class="ttd-img"[^>]*>[\s\S]*?<img/i', $html);
        $this->assertStringContainsString('alt="Tanda tangan"', $html);
    }

    public function test_publish_stores_signature_png_on_disk(): void
    {
        Storage::fake('local');
        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application));

        $application->refresh();
        $letter = $application->generatedLetter;
        $this->assertNotNull($letter);
        $this->assertNotNull($letter->signature_path);
        Storage::disk('local')->assertExists($letter->signature_path);
        Storage::disk('local')->assertExists($letter->file_path);
    }

    public function test_compose_page_prefills_signature_from_published_letter(): void
    {
        Storage::fake('local');
        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->withoutVite();

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ]);

        $response = $this->actingAs($staff)
            ->get(route('rt.applications.letter.compose', $application));

        $response->assertOk();
        $this->assertStringContainsString('data:image/png;base64,', $response->getContent());
        $this->assertStringContainsString('id="signature_data"', $response->getContent());
    }

    public function test_sample_signature_is_not_considered_blank(): void
    {
        $this->assertFalse(\App\Support\SignatureStorage::isBlank($this->sampleSignatureDataUri()));
    }

    public function test_compose_page_shows_whatsapp_button_when_pdf_published(): void
    {
        Storage::fake('local');
        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->withoutVite();

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application));

        $this->actingAs($staff)
            ->get(route('rt.applications.letter.compose', $application))
            ->assertOk()
            ->assertSee('Kirim WhatsApp', false)
            ->assertSee(route('rt.applications.letter.whatsapp', $application, false));
    }

    public function test_send_letter_whatsapp_posts_pdf_via_waha(): void
    {
        Storage::fake('local');
        config([
            'waha.api_key' => 'test-waha-key',
            'waha.base_url' => 'http://waha:3000',
            'waha.session' => 'default',
        ]);

        Http::fake([
            'http://waha:3000/api/sessions/default' => Http::response([
                'name' => 'default',
                'status' => 'WORKING',
            ]),
            'http://waha:3000/api/sendFile' => Http::response(['id' => 'wa-msg-pdf-1'], 200),
        ]);

        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application));

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.whatsapp', $application))
            ->assertRedirect(route('rt.applications.letter.compose', $application))
            ->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/sendFile');
        });

        $log = NotificationLog::query()
            ->where('application_id', $application->id)
            ->where('event', 'letter_sent')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertStringNotContainsString('/surat/'.$application->id.'/unduh', $log->message);
        $this->assertStringNotContainsString('Unduh surat:', $log->message);
        $this->assertStringContainsString('terlampir', $log->message);

        $this->assertDatabaseHas('notification_logs', [
            'application_id' => $application->id,
            'event' => 'letter_sent',
            'status' => 'sent',
        ]);
    }

    public function test_send_letter_whatsapp_fallback_text_includes_download_link_once(): void
    {
        Storage::fake('local');
        config([
            'waha.api_key' => 'test-waha-key',
            'waha.base_url' => 'http://waha:3000',
            'waha.session' => 'default',
            'app.url' => 'http://localhost',
        ]);

        Http::fake([
            'http://waha:3000/api/sessions/default' => Http::response([
                'name' => 'default',
                'status' => 'WORKING',
            ]),
            'http://waha:3000/api/sendFile' => Http::response(['error' => 'send failed'], 500),
            'http://waha:3000/api/sendText' => Http::response(['id' => 'wa-msg-text-1'], 200),
        ]);

        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application));

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.whatsapp', $application))
            ->assertRedirect(route('rt.applications.letter.compose', $application))
            ->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/sendText');
        });

        $log = NotificationLog::query()
            ->where('application_id', $application->id)
            ->where('event', 'letter_sent')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $downloadPath = '/surat/'.$application->id.'/unduh';
        $this->assertStringContainsString($downloadPath, $log->message);
        $this->assertSame(1, substr_count($log->message, $downloadPath));
        $this->assertSame(1, substr_count($log->message, 'Unduh surat:'));
    }

    public function test_public_letter_download_with_valid_signed_url(): void
    {
        Storage::fake('local');
        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->actingAs($staff)
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application));

        $application->refresh()->load('generatedLetter');
        $url = LetterDownloadLink::signedUrl($application);

        $this->assertNotNull($url);
        $this->assertStringContainsString('/surat/'.$application->id.'/unduh', $url);
        $this->assertStringContainsString('signature=', $url);

        $signedQuery = parse_url($url, PHP_URL_QUERY);

        $this->get('/surat/'.$application->id.'/unduh?'.$signedQuery)
            ->assertOk()
            ->assertDownload('surat-'.$application->application_number.'.pdf');

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ValidateSignature::class)
            ->get(route('public.letter.download', ['application' => $application->id]))
            ->assertOk()
            ->assertDownload('surat-'.$application->application_number.'.pdf');
    }

    public function test_public_letter_download_rejects_invalid_signature(): void
    {
        Storage::fake('local');
        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->actingAs($staff)
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application));

        $this->get(route('public.letter.download', ['application' => $application->id]))
            ->assertForbidden();
    }

    public function test_public_letter_download_rejects_expired_signature(): void
    {
        Storage::fake('local');
        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        [, $staff, $application] = $this->createLetterReadyApplication();

        $this->actingAs($staff)
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application));

        $expiredUrl = URL::temporarySignedRoute(
            'public.letter.download',
            now()->subMinute(),
            ['application' => $application->id],
        );

        $expiredQuery = parse_url($expiredUrl, PHP_URL_QUERY);

        $this->get(route('public.letter.download', ['application' => $application->id], false).'?'.$expiredQuery)
            ->assertForbidden();
    }

    public function test_send_letter_whatsapp_fails_without_signature(): void
    {
        Storage::fake('local');
        [, $staff, $application] = $this->createLetterReadyApplication();

        $pdfPath = 'letters/test-unsigned.pdf';
        Storage::disk('local')->put($pdfPath, '%PDF-1.4 test');

        GeneratedLetter::create([
            'application_id' => $application->id,
            'letter_template_id' => LetterTemplate::first()->id,
            'file_path' => $pdfPath,
            'letter_number' => 'RT008/06/2026/0001',
            'letter_fields' => [],
            'signature_path' => null,
            'signed_at' => null,
            'issued_at' => now(),
        ]);

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.whatsapp', $application))
            ->assertRedirect(route('rt.applications.letter.compose', $application))
            ->assertSessionHasErrors('letter');

        $this->assertDatabaseHas('notification_logs', [
            'application_id' => $application->id,
            'event' => 'letter_sent',
            'status' => 'failed',
        ]);
    }

    public function test_send_letter_whatsapp_skips_when_no_phone(): void
    {
        Storage::fake('local');
        Http::fake();

        [, $staff, $application] = $this->createLetterReadyApplication();
        $application->resident->update(['phone' => null]);

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ])
            ->assertRedirect(route('rt.applications.letter.compose', $application));

        $this->withoutVite();

        $this->actingAs($staff)
            ->get(route('rt.applications.letter.compose', $application))
            ->assertOk()
            ->assertSee('Nomor HP warga belum terdaftar', false);

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'api.fonnte.com/send');
        });
    }

    public function test_compose_page_shows_whatsapp_skipped_status(): void
    {
        Storage::fake('local');
        [, $staff, $application] = $this->createLetterReadyApplication();

        NotificationLog::create([
            'application_id' => $application->id,
            'resident_id' => $application->resident_id,
            'phone' => '',
            'event' => 'letter_sent',
            'message' => 'Test',
            'status' => 'skipped',
            'error_message' => 'Notifikasi WA nonaktif atau nomor kosong',
        ]);

        $this->actingAs($staff)
            ->from(route('rt.applications.letter.compose', $application))
            ->post(route('rt.applications.letter.publish', $application), [
                'fields' => $this->sampleFields(),
                'signature_data' => $this->sampleSignatureDataUri(),
            ]);

        $this->withoutVite();

        $this->actingAs($staff)
            ->get(route('rt.applications.letter.compose', $application))
            ->assertOk()
            ->assertSee('Dilewati', false);
    }

    /** @return array{0: RtProfile, 1: User, 2: Application} */
    private function prepareUsahaApplication(): array
    {
        [$profile, $staff, $application] = $this->createLetterReadyApplication();

        $service = ServiceType::where('code', 'surat_usaha')->first()
            ?? ServiceType::create([
                'code' => 'surat_usaha',
                'name' => 'Surat Pengantar Usaha (SKU)',
                'is_active' => true,
            ]);

        $application->update(['service_type_id' => $service->id]);

        LetterTemplate::updateOrCreate(
            ['service_type_id' => $service->id],
            [
                'name' => 'Template Usaha',
                'body_html' => SuratPengantarTemplate::bodyHtml(),
                'is_active' => true,
            ]
        );

        return [$profile, $staff, $application->fresh(['serviceType', 'resident.household.rtProfile'])];
    }

    /** @return array<string, string> */
    private function sampleUsahaFields(): array
    {
        return [
            'nama_usaha' => 'Toko Makmur',
            'jenis_usaha' => 'Perdagangan eceran',
            'alamat_usaha' => 'Jl. Usaha RT 008 No. 3',
        ];
    }

    private function sampleSignatureDataUri(): string
    {
        $image = imagecreatetruecolor(200, 80);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);
        imagefilledellipse($image, 100, 40, 120, 40, $black);
        imagestring($image, 5, 40, 32, 'TTD', $black);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png ?: '');
    }
}
