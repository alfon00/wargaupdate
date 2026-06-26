<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Household;
use App\Models\NotificationLog;
use App\Models\PendataanDocument;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\User;
use App\Services\PendataanDocumentStorage;
use App\Services\ResidentDataIndexService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RtResidentDataIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Household, 2: Resident, 3: Resident} */
    private function seedHouseholdWithMembers(): array
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-data-warga@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010001',
            'address' => 'Jl. Merpati RT 008',
            'status_rumah_tinggal' => 'Milik sendiri',
            'kondisi_rumah_milik' => 'layak',
            'suku' => 'Amungme',
        ]);

        $active = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010099',
            'name' => 'Budi Aktif',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-15',
            'gender' => 'Laki-laki',
            'occupation' => 'Wiraswasta',
            'education' => 'SMA/SMK',
            'religion' => 'Islam',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $archived = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010088',
            'name' => 'Siti Arsip',
            'domicile_status' => DomicileStatus::PindahKeluar,
        ]);

        return [$staff, $household, $active, $archived];
    }

    private function fakeWahaWorking(): void
    {
        Http::fake([
            'http://waha:3000/api/sessions/default' => Http::response([
                'name' => 'default',
                'status' => 'WORKING',
            ]),
            'http://waha:3000/api/sendText' => Http::response(['id' => 'wa-msg-1'], 200),
        ]);
    }

    /** @return array<string, mixed> */
    private function residentUpdatePayload(Household $household, Resident $resident): array
    {
        return [
            'nik' => $resident->nik,
            'name' => $resident->name,
            'birth_place' => $resident->birth_place,
            'birth_date' => $resident->birth_date?->format('Y-m-d'),
            'gender' => $resident->gender,
            'religion' => $resident->religion,
            'occupation' => $resident->occupation,
            'education' => $resident->education,
            'marital_status' => $resident->marital_status,
            'citizenship' => $resident->citizenship ?: 'WNI',
            'phone' => $resident->phone,
            'relationship_to_head' => $resident->relationship_to_head ?: ($resident->is_head_of_family ? 'Kepala Keluarga' : ''),
            'is_head_of_family' => $resident->is_head_of_family ? '1' : '0',
            'whatsapp_notify' => '1',
            'family_card_number' => $household->family_card_number,
            'address' => $household->address,
            'house_number' => $household->house_number,
            'status_rumah_tinggal' => $household->status_rumah_tinggal,
            'suku' => $household->suku,
            'kondisi_rumah_milik' => $household->kondisi_rumah_milik,
        ];
    }

    public function test_combined_data_page_renders(): void
    {
        [$staff, , $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['filter' => 'semua']))
            ->assertOk()
            ->assertSee('Data warga lengkap')
            ->assertSee('lw-rt-data-resident-household-table', false)
            ->assertDontSee('data-expand-household', false)
            ->assertDontSee('id="kk-detail-', false)
            ->assertDontSee('class="lw-rt-data-kk-table"', false)
            ->assertSee('Budi Aktif')
            ->assertSee('3201010101010001')
            ->assertSee('Detail', false)
            ->assertDontSee('Edit KK', false)
            ->assertDontSee(route('rt.residents.edit', $active), false)
            ->assertDontSee('data-delete-action="'.route('rt.residents.destroy', $active).'"', false)
            ->assertSee(route('rt.residents.show', ['resident' => $active]), false);
    }

    public function test_resident_show_shows_surat_readiness_callout_when_documents_missing(): void
    {
        [$staff, , $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.residents.show', $active))
            ->assertOk()
            ->assertSee('Verifikasi surat online: Perlu KTP/KIA')
            ->assertSee('belum diunggah');
    }

    public function test_resident_edit_shows_surat_readiness_callout_when_documents_missing(): void
    {
        [$staff, , $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.residents.edit', $active))
            ->assertOk()
            ->assertDontSee('Verifikasi surat online: Perlu KTP/KIA');
    }

    public function test_index_uses_table_layout_and_category_chips(): void
    {
        [$staff, , $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['q' => '3201010101010001']))
            ->assertOk()
            ->assertSee('lw-rt-data-resident-household-table', false)
            ->assertDontSee('data-expand-household', false)
            ->assertDontSee('id="kk-detail-', false)
            ->assertDontSee('class="lw-rt-data-kk-table"', false)
            ->assertSee('data-rt-data-warga-table', false)
            ->assertDontSee('data-rt-data-actions-menu', false)
            ->assertDontSee('Menu aksi lainnya', false)
            ->assertDontSee(route('rt.residents.edit', $active), false)
            ->assertDontSee('data-delete-action="'.route('rt.residents.destroy', $active).'"', false)
            ->assertSee('No. Kartu Keluarga')
            ->assertSee('Nama')
            ->assertSee('NIK', false)
            ->assertSee('Entri RT')
            ->assertSee('Warga baru')
            ->assertDontSee('Mode pencarian')
            ->assertSee('Daftar KK')
            ->assertDontSee('class="lw-rt-data-residents-table"', false)
            ->assertDontSee('+ Tambah KK')
            ->assertDontSee('+ Tambah warga');
    }

    public function test_resident_show_displays_pendataan_documents(): void
    {
        [$staff, $household, $head] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        $household->update(['pendataan_category' => 'warga_baru']);

        $document = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'kk',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/test-kk.pdf',
            'original_name' => 'kk-scan.pdf',
            'mime_type' => 'application/pdf',
        ]);

        Storage::disk('local')->put($document->file_path, 'dummy');

        $this->actingAs($staff)
            ->get(route('rt.residents.show', $head))
            ->assertOk()
            ->assertSee('Lampiran berkas (1)', false)
            ->assertSee('lw-rt-doc-card-badge', false)
            ->assertSee('>KK<', false)
            ->assertSee('kk-scan.pdf')
            ->assertSee('lw-rt-doc-grid', false)
            ->assertSee('lw-rt-doc-card', false)
            ->assertSee('lw-rt-doc-card-media', false)
            ->assertSee(route('rt.pendataan.document.view', [$head, $document]), false);
    }

    public function test_resident_show_renders_image_modal_trigger_for_lampiran_photo(): void
    {
        [$staff, $household, $head] = $this->seedHouseholdWithMembers();

        Storage::fake('local');
        $household->update(['pendataan_category' => 'warga_baru']);

        $imageDoc = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'lampiran',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/foto-rumah.jpg',
            'original_name' => 'foto-rumah.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        Storage::disk('local')->put($imageDoc->file_path, 'dummy-image');

        $this->actingAs($staff)
            ->get(route('rt.residents.show', $head))
            ->assertOk()
            ->assertSee('lw-rt-doc-modal-trigger', false)
            ->assertSee('data-doc-image-url="'.route('rt.pendataan.document.view', [$head, $imageDoc]).'"', false);
    }

    public function test_lampiran_shows_view_button_when_head_archived_but_household_in_aktif_filter(): void
    {
        Storage::fake('local');

        $profile = RtProfile::create([
            'rt_number' => '009',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 9',
            'email' => 'ketua-rt9@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010090',
            'address' => 'Jl. Test RT 009',
            'status_rumah_tinggal' => 'Milik sendiri',
            'kondisi_rumah_milik' => 'layak',
            'suku' => 'Amungme',
        ]);

        $archivedHead = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010091',
            'name' => 'Kepala Arsip',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-15',
            'gender' => 'Laki-laki',
            'occupation' => 'Pekerja',
            'education' => 'SMA/SMK',
            'religion' => 'Islam',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'domicile_status' => DomicileStatus::PindahKeluar,
            'is_head_of_family' => true,
        ]);

        // Ada anggota aktif sehingga rumah tangga ikut muncul di filter "aktif", tapi kepala tidak ikut karena statusnya arsip.
        $activeMember = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010092',
            'name' => 'Anggota Aktif',
            'birth_place' => 'Timika',
            'birth_date' => '1992-01-15',
            'gender' => 'Perempuan',
            'occupation' => 'Ibu Rumah Tangga',
            'education' => 'SMA/SMK',
            'religion' => 'Islam',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => false,
        ]);

        $household->update(['pendataan_category' => 'warga_baru']);

        $document = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'kk',
            'file_path' => 'pendataan/rt-'.$profile->id.'/household-'.$household->id.'/kk-arsip.pdf',
            'original_name' => 'kk-arsip.pdf',
            'mime_type' => 'application/pdf',
        ]);

        Storage::disk('local')->put($document->file_path, 'dummy');

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['filter' => 'aktif', 'q' => '3201010101010090']))
            ->assertOk()
            ->assertSee('Anggota Aktif');

        $this->actingAs($staff)
            ->get(route('rt.residents.show', $activeMember))
            ->assertOk()
            ->assertSee(route('rt.pendataan.document.view', [$archivedHead, $document]), false);

        $this->actingAs($staff)
            ->get(route('rt.pendataan.document.view', [$archivedHead, $document]))
            ->assertOk();
    }

    public function test_pendataan_show_uses_document_card_grid(): void
    {
        [$staff, $household, $head] = $this->seedHouseholdWithMembers();

        $household->update([
            'pendataan_category' => 'warga_baru',
            'status' => 'menunggu_verifikasi',
        ]);
        $head->update(['domicile_status' => DomicileStatus::MenungguVerifikasi]);

        PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'kk',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/show-kk.pdf',
            'original_name' => 'kk-verifikasi.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($staff)
            ->get(route('rt.pendataan.show', $head));

        $response->assertOk()
            ->assertSee('lw-rt-doc-grid--full', false)
            ->assertSee('lw-rt-doc-card', false)
            ->assertSee('kk-verifikasi.pdf')
            ->assertSee('lw-panel-form--sidebar', false)
            ->assertDontSee('style="width:100%"', false);
    }

    public function test_data_warga_report_pdf_endpoint_returns_pdf(): void
    {
        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.report', ['filter' => 'semua']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_data_warga_report_pdf_groups_residents_by_household(): void
    {
        [$staff, $household] = $this->seedHouseholdWithMembers();

        $html = view('rt.resident-data.report-pdf', [
            'rt' => $household->rtProfile,
            'households' => app(ResidentDataIndexService::class)
                ->buildHouseholdQuery($household->rtProfile, 'semua', 'semua', '')
                ->get()
                ->filter(fn ($item) => $item->residents->isNotEmpty())
                ->values(),
            'filter' => 'semua',
            'kategori' => 'semua',
            'kategoriLabel' => 'Semua',
            'search' => '',
            'totalHouseholds' => 1,
            'totalResidents' => 2,
            'generatedAt' => now('Asia/Jayapura'),
        ])->render();

        $this->assertStringContainsString('Keluarga 1', $html);
        $this->assertStringContainsString($household->family_card_number, $html);
        $this->assertStringContainsString('Anggota keluarga (2)', $html);
        $this->assertStringContainsString('Budi Aktif', $html);
        $this->assertStringContainsString('Siti Arsip', $html);
        $this->assertStringContainsString('3201010101010099', $html);
    }

    public function test_pendataan_document_storage_uses_structured_private_path(): void
    {
        Storage::fake('local');

        $profile = RtProfile::create([
            'rt_number' => '010',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT 10',
        ]);

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010010',
            'address' => 'Jl. Test',
            'pendataan_category' => 'warga_baru',
        ]);

        $document = app(PendataanDocumentStorage::class)->store(
            $household,
            UploadedFile::fake()->create('kartu-keluarga.pdf', 100, 'application/pdf'),
            'kk',
        );

        $expectedDir = "pendataan/rt-{$profile->id}/household-{$household->id}";
        $this->assertStringStartsWith($expectedDir.'/', $document->file_path);
        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_filter_kategori_entri_rt(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '009',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT 9',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT 9',
            'email' => 'ketua-rt9@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $entriRt = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101011001',
            'address' => 'Jl. Entri RT',
            'pendataan_category' => '',
        ]);

        Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101011002',
            'address' => 'Jl. Pendataan',
            'pendataan_category' => 'warga_baru',
        ]);

        Resident::create([
            'household_id' => $entriRt->id,
            'name' => 'Kepala Entri',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['kategori' => 'entri_rt', 'filter' => 'semua', 'q' => '3201010101011001']))
            ->assertOk()
            ->assertSee('3201010101011001')
            ->assertSee('Kepala Entri')
            ->assertDontSee('3201010101011002');
    }

    public function test_resident_show_displays_demographics(): void
    {
        [$staff, , $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.residents.show', $active))
            ->assertOk()
            ->assertSee('Pekerjaan')
            ->assertSee('Wiraswasta')
            ->assertSee('Agama')
            ->assertSee('Islam');
    }

    public function test_member_status_filter_aktif_hides_archived(): void
    {
        [$staff, , , $archived] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['filter' => 'aktif', 'q' => 'Budi']))
            ->assertOk()
            ->assertSee('Budi Aktif')
            ->assertDontSee('id="resident-row-'.$archived->id.'"', false);
    }

    public function test_member_status_filter_arsip_shows_archived_only(): void
    {
        [$staff, , $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['filter' => 'arsip', 'q' => 'Siti']))
            ->assertOk()
            ->assertSee('Siti Arsip')
            ->assertDontSee('id="resident-row-'.$active->id.'"', false);
    }

    public function test_index_shows_table_when_search_query_is_empty(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['filter' => 'semua']))
            ->assertOk()
            ->assertSee('lw-rt-data-resident-household-table', false)
            ->assertDontSee('data-expand-household', false)
            ->assertDontSee('id="kk-detail-', false)
            ->assertDontSee('class="lw-rt-data-kk-table"', false)
            ->assertSee('Budi Aktif')
            ->assertSee($household->family_card_number)
            ->assertSee('Detail', false)
            ->assertSee(route('rt.residents.show', ['resident' => $active]), false);
    }

    public function test_index_highlights_focused_household_row(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', [
                'filter' => 'aktif',
                'household' => $household->id,
            ]))
            ->assertOk()
            ->assertSee('id="resident-row-'.$active->id.'"', false)
            ->assertSee('lw-rt-data-resident-row is-focused', false)
            ->assertDontSee('id="kk-detail-'.$household->id.'"', false)
            ->assertDontSee('data-expand-household', false);
    }

    public function test_index_search_by_kk_shows_all_household_members_as_rows(): void
    {
        [$staff, $household] = array_slice($this->seedHouseholdWithMembers(), 0, 2);

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', [
                'filter' => 'semua',
                'q' => $household->family_card_number,
            ]))
            ->assertOk()
            ->assertSee('Budi Aktif')
            ->assertSee('Siti Arsip')
            ->assertSee('3201010101010099')
            ->assertSee('3201010101010088');
    }

    public function test_index_search_by_nik_shows_only_matching_resident(): void
    {
        [$staff, , $active, $archived] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', [
                'filter' => 'semua',
                'q' => $archived->nik,
            ]))
            ->assertOk()
            ->assertSee('Siti Arsip')
            ->assertDontSee('id="resident-row-'.$active->id.'"', false);
    }

    /** @return array{0: User, 1: list<Household>} */
    private function seedManyHouseholds(int $count): array
    {
        $profile = RtProfile::create([
            'rt_number' => '009',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-many-kk@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $households = [];

        for ($i = 1; $i <= $count; $i++) {
            $household = Household::create([
                'rt_profile_id' => $profile->id,
                'family_card_number' => sprintf('320101010101%04d', $i),
                'address' => "Jl. Merpati RT 009 No. {$i}",
                'status_rumah_tinggal' => 'Milik sendiri',
                'kondisi_rumah_milik' => 'layak',
                'suku' => 'Amungme',
            ]);

            Resident::create([
                'household_id' => $household->id,
                'nik' => sprintf('320101010101%04d', $i),
                'name' => "Kepala {$i}",
                'domicile_status' => DomicileStatus::Aktif,
                'is_head_of_family' => true,
            ]);

            $households[] = $household;
        }

        return [$staff, $households];
    }

    public function test_index_redirects_to_page_containing_focused_household(): void
    {
        [$staff, $households] = $this->seedManyHouseholds(25);
        $target = $households[24];

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', [
                'filter' => 'aktif',
                'household' => $target->id,
            ]))
            ->assertRedirect(route('rt.data-warga.index', [
                'filter' => 'aktif',
                'household' => $target->id,
                'page' => 2,
            ]));
    }

    public function test_resident_show_page_displays_full_profile(): void
    {
        [$staff, $household, $active, $archived] = $this->seedHouseholdWithMembers();

        $response = $this->actingAs($staff)
            ->get(route('rt.residents.show', [
                'resident' => $active,
                'filter' => 'aktif',
                'household' => $active->household_id,
            ]));

        $content = $response->getContent();

        $response->assertOk()
            ->assertSee('lw-rt-resident-detail-page', false)
            ->assertSee('lw-rt-unified-kk', false)
            ->assertSee('lw-rt-resident-detail-table', false)
            ->assertSee('lw-panel-btn--sm', false)
            ->assertSee('lw-rt-delete-modal', false)
            ->assertDontSee('Detail warga', false)
            ->assertDontSee('Data kartu keluarga &amp; anggota', false)
            ->assertSee('Daftar Anggota Keluarga', false)
            ->assertSee('No. Kartu Keluarga', false)
            ->assertSee('lw-rt-household-members-panel', false)
            ->assertSee('Status dalam Keluarga', false)
            ->assertSee('Jenis Kelamin', false)
            ->assertDontSee('Cari anggota (nama / NIK)', false)
            ->assertDontSee('data-rt-members-search', false)
            ->assertDontSee('data-rt-members-gender', false)
            ->assertDontSee('data-rt-members-status', false)
            ->assertDontSee('lw-rt-household-members-toolbar', false)
            ->assertSee('Detail anggota:', false)
            ->assertDontSee('Sedang dilihat', false)
            ->assertSee('Siti Arsip', false)
            ->assertSee('Perlu KTP/KIA')
            ->assertDontSee('Kartu keluarga', false)
            ->assertSee('Kartu Keluarga 3201010101010001', false)
            ->assertSee('Identitas warga', false)
            ->assertSee('Sosial & pendidikan', false)
            ->assertSee('Kontak & sistem', false)
            ->assertSee('Budi Aktif', false)
            ->assertSee('Terdata (aktif)', false)
            ->assertSee('3201010101010099', false)
            ->assertSee('3201010101010001', false)
            ->assertSee('+ Tambah anggota', false)
            ->assertSee('/rt/residents/create?', false)
            ->assertSee('household_id='.$active->household_id, false)
            ->assertSee('resident='.$active->id, false)
            ->assertSee('>Edit</a>', false)
            ->assertDontSee('Edit data', false)
            ->assertDontSee('Edit KK', false)
            ->assertDontSee('Kembali ke daftar', false)
            ->assertSee('← Kembali ke data warga', false)
            ->assertSee('lw-panel-page-back', false)
            ->assertSee('/rt/data-warga?filter=aktif&amp;household='.$active->household_id, false)
            ->assertDontSee('Kelola data warga', false)
            ->assertSee('lw-rt-doc-edit-hint', false)
            ->assertSee('Berkas identitas anggota dapat diperbarui', false)
            ->assertSee('scan KK dan lampiran tambahan juga dapat diperbarui', false)
            ->assertDontSee('id="kelola-data-warga"', false)
            ->assertDontSee('data-delete-action="'.route('rt.residents.destroy', $archived).'"', false)
            ->assertDontSee('data-delete-action="'.route('rt.residents.destroy', $active).'"', false)
            ->assertDontSee(route('rt.households.edit', $household), false);

        $this->assertLessThan(
            strpos($content, 'Identitas warga'),
            strpos($content, 'Daftar Anggota Keluarga'),
            'Daftar Anggota Keluarga harus muncul sebelum Identitas warga'
        );

        $this->assertLessThan(
            strpos($content, 'Lampiran berkas'),
            strpos($content, 'Detail anggota:'),
            'Detail anggota harus muncul sebelum lampiran berkas'
        );

        $editPath = '/rt/residents/'.$active->id.'/edit?filter=aktif&amp;household='.$active->household_id;
        $response->assertSee($editPath, false);

        $this->assertMatchesRegularExpression(
            '/Detail anggota:.*?'.preg_quote($editPath, '/').'.*?Identitas warga/s',
            $content,
            'Tombol Edit harus berada di section Detail anggota sebelum profil'
        );

        $unifiedKkStart = strpos($content, '<div class="lw-rt-unified-kk">');
        $this->assertNotFalse($unifiedKkStart);
        $this->assertStringNotContainsString(
            $editPath,
            substr($content, 0, $unifiedKkStart),
            'Tombol Edit tidak boleh di page header bila punya KK'
        );
    }

    public function test_resident_edit_page_matches_detail_layout(): void
    {
        [$staff, , $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.residents.edit', [
                'resident' => $active,
                'filter' => 'aktif',
                'household' => $active->household_id,
            ]))
            ->assertOk()
            ->assertSee('lw-rt-resident-edit-page', false)
            ->assertSee('Budi Aktif', false)
            ->assertSee('lw-panel-btn--sm', false)
            ->assertSee('/rt/residents/'.$active->id.'?filter=aktif&amp;household='.$active->household_id, false)
            ->assertDontSee('>Detail</a>', false)
            ->assertDontSee('← Kembali ke data warga', false)
            ->assertSee('Zona berbahaya', false)
            ->assertSee('Edit warga', false)
            ->assertSee('lw-rt-resident-detail-table', false)
            ->assertSee('Kartu keluarga', false)
            ->assertSee('Identitas warga', false)
            ->assertSee('Sosial & pendidikan', false)
            ->assertSee('Kontak & sistem', false)
            ->assertSee('Hubungan dalam KK', false)
            ->assertSee('name="relationship_to_head"', false)
            ->assertSee('name="family_card_number"', false)
            ->assertSee('name="suku"', false)
            ->assertDontSee('lw-panel-form-legend">Demografi', false)
            ->assertSee('Terakhir diperbarui:', false)
            ->assertSee('lw-rt-resident-last-updated', false)
            ->assertSee('lw-panel-form--in-card', false)
            ->assertSee('lw-rt-edit-documents', false)
            ->assertSee('Lampiran berkas', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="document_identity"', false)
            ->assertSee('name="document_kk"', false)
            ->assertSee('name="documents[]"', false);
    }

    public function test_resident_edit_page_shows_document_upload_fields(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        $ktp = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'ktp_kepala',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/ktp.jpg',
            'original_name' => 'ktp-budi.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        Storage::disk('local')->put($ktp->file_path, 'dummy');

        $lampiran = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'lampiran',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/foto.jpg',
            'original_name' => 'foto-rumah.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        Storage::disk('local')->put($lampiran->file_path, 'dummy');

        PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'kk',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/kk.pdf',
            'original_name' => 'kk.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($staff)
            ->get(route('rt.residents.edit', $active))
            ->assertOk()
            ->assertSee('lw-rt-edit-documents', false)
            ->assertSee('lw-rt-edit-doc-upload', false)
            ->assertSee('Unggah berkas identitas anggota', false)
            ->assertSee('Berkas identitas anggota ini', false)
            ->assertSee('ktp-budi.jpg', false)
            ->assertSee('lw-rt-doc-card-badge', false)
            ->assertSee('lw-rt-doc-card-media', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('Ganti scan/foto KTP (opsional)', false)
            ->assertSee('name="document_identity"', false)
            ->assertSee('name="remove_identity_document[]"', false)
            ->assertSee('Hapus berkas ini', false)
            ->assertSee('lw-rt-edit-doc-household', false)
            ->assertSee('Berkas keluarga (KK &amp; lampiran)', false)
            ->assertSee('foto-rumah.jpg', false)
            ->assertSee('kk.pdf', false)
            ->assertSee('Ganti scan/foto KK (opsional)', false)
            ->assertSee('name="document_kk"', false)
            ->assertSee('name="documents[]"', false)
            ->assertSee('name="remove_household_document[]"', false);
    }

    public function test_non_head_edit_does_not_show_kk_fields(): void
    {
        [$staff, $household, , $archived] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'kk',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/kk.pdf',
            'original_name' => 'kk.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($staff)
            ->get(route('rt.residents.edit', $archived))
            ->assertOk()
            ->assertDontSee('lw-rt-edit-doc-household', false)
            ->assertDontSee('Ganti scan/foto KK', false)
            ->assertDontSee('name="document_kk"', false)
            ->assertDontSee('name="documents[]"', false)
            ->assertDontSee('name="remove_household_document[]"', false);
    }

    public function test_resident_edit_page_shows_only_non_head_member_identity_document(): void
    {
        [$staff, $household, , $archived] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'kk',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/kk.pdf',
            'original_name' => 'kk.pdf',
            'mime_type' => 'application/pdf',
        ]);

        PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'ktp_kepala',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/ktp-kepala.jpg',
            'original_name' => 'ktp-kepala.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $memberKtp = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'ktp_a1',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/ktp-siti.jpg',
            'original_name' => 'ktp-siti.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        Storage::disk('local')->put($memberKtp->file_path, 'dummy');

        $this->actingAs($staff)
            ->get(route('rt.residents.edit', $archived))
            ->assertOk()
            ->assertSee('ktp-siti.jpg', false)
            ->assertDontSee('ktp-kepala.jpg', false)
            ->assertDontSee('kk.pdf', false);
    }

    public function test_resident_update_replaces_member_ktp(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        $oldKk = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'kk',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/kk.pdf',
            'original_name' => 'kk.pdf',
            'mime_type' => 'application/pdf',
        ]);
        Storage::disk('local')->put($oldKk->file_path, 'kk-content');

        $oldKtp = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'ktp_kepala',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/old-ktp.jpg',
            'original_name' => 'ktp-lama.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        Storage::disk('local')->put($oldKtp->file_path, 'old-ktp');

        $this->actingAs($staff)
            ->put(route('rt.residents.update', $active), array_merge(
                $this->residentUpdatePayload($household, $active),
                ['document_identity' => UploadedFile::fake()->create('ktp-baru.jpg', 100, 'image/jpeg')]
            ))
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]));

        $this->assertDatabaseHas('pendataan_documents', ['id' => $oldKk->id]);
        $this->assertDatabaseMissing('pendataan_documents', ['id' => $oldKtp->id]);
        $this->assertFalse(Storage::disk('local')->exists($oldKtp->file_path));

        $newKtp = PendataanDocument::query()
            ->where('household_id', $household->id)
            ->where('document_type', 'ktp_kepala')
            ->first();

        $this->assertNotNull($newKtp);
        $this->assertSame('ktp-baru.jpg', $newKtp->original_name);
        $this->assertTrue(Storage::disk('local')->exists($newKtp->file_path));
    }

    public function test_resident_update_replaces_non_head_member_ktp(): void
    {
        [$staff, $household, , $archived] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        $oldKtp = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'ktp_a1',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/old-siti.jpg',
            'original_name' => 'ktp-siti-lama.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        Storage::disk('local')->put($oldKtp->file_path, 'old-ktp');

        $this->actingAs($staff)
            ->put(route('rt.residents.update', $archived), array_merge(
                $this->residentUpdatePayload($household, $archived),
                ['document_identity' => UploadedFile::fake()->create('ktp-siti-baru.jpg', 100, 'image/jpeg')]
            ))
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]));

        $this->assertDatabaseMissing('pendataan_documents', ['id' => $oldKtp->id]);

        $newKtp = PendataanDocument::query()
            ->where('household_id', $household->id)
            ->where('document_type', 'ktp_a1')
            ->first();

        $this->assertNotNull($newKtp);
        $this->assertSame('ktp-siti-baru.jpg', $newKtp->original_name);
    }

    public function test_resident_update_deletes_member_identity_document(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        $ktp = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'ktp_kepala',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/hapus.jpg',
            'original_name' => 'hapus.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        Storage::disk('local')->put($ktp->file_path, 'dummy');

        $this->actingAs($staff)
            ->put(route('rt.residents.update', $active), array_merge(
                $this->residentUpdatePayload($household, $active),
                ['remove_identity_document' => [$ktp->id]]
            ))
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]));

        $this->assertDatabaseMissing('pendataan_documents', ['id' => $ktp->id]);
        $this->assertFalse(Storage::disk('local')->exists($ktp->file_path));
    }

    public function test_resident_update_rejects_foreign_identity_document_delete(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        $otherProfile = RtProfile::create([
            'rt_number' => '099',
            'rw_number' => '099',
            'kelurahan' => 'Kelurahan Lain',
            'ketua_rt' => 'Ketua RT Lain',
        ]);

        $otherHousehold = Household::create([
            'rt_profile_id' => $otherProfile->id,
            'family_card_number' => '3201010101010999',
            'address' => 'Jl. Lain',
        ]);

        $foreignDoc = PendataanDocument::create([
            'household_id' => $otherHousehold->id,
            'document_type' => 'ktp_kepala',
            'file_path' => 'pendataan/rt-'.$otherProfile->id.'/household-'.$otherHousehold->id.'/foreign.jpg',
            'original_name' => 'foreign.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $this->actingAs($staff)
            ->put(route('rt.residents.update', $active), array_merge(
                $this->residentUpdatePayload($household, $active),
                ['remove_identity_document' => [$foreignDoc->id]]
            ))
            ->assertSessionHasErrors('remove_identity_document');

        $this->assertDatabaseHas('pendataan_documents', ['id' => $foreignDoc->id]);
    }

    public function test_resident_update_replaces_kk_scan(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        $oldKk = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'kk',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/old-kk.pdf',
            'original_name' => 'kk-lama.pdf',
            'mime_type' => 'application/pdf',
        ]);
        Storage::disk('local')->put($oldKk->file_path, 'old-kk');

        $this->actingAs($staff)
            ->put(route('rt.residents.update', $active), array_merge(
                $this->residentUpdatePayload($household, $active),
                ['document_kk' => UploadedFile::fake()->create('kk-baru.pdf', 100, 'application/pdf')]
            ))
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]));

        $this->assertDatabaseMissing('pendataan_documents', ['id' => $oldKk->id]);
        $this->assertFalse(Storage::disk('local')->exists($oldKk->file_path));

        $newKk = PendataanDocument::query()
            ->where('household_id', $household->id)
            ->where('document_type', 'kk')
            ->first();

        $this->assertNotNull($newKk);
        $this->assertSame('kk-baru.pdf', $newKk->original_name);
        $this->assertTrue(Storage::disk('local')->exists($newKk->file_path));
    }

    public function test_resident_update_appends_lampiran(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        $this->actingAs($staff)
            ->put(route('rt.residents.update', $active), array_merge(
                $this->residentUpdatePayload($household, $active),
                ['documents' => [
                    UploadedFile::fake()->create('foto-rumah.jpg', 100, 'image/jpeg'),
                    UploadedFile::fake()->create('surat-kontrak.pdf', 100, 'application/pdf'),
                ]]
            ))
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]));

        $lampiran = PendataanDocument::query()
            ->where('household_id', $household->id)
            ->where('document_type', 'lampiran')
            ->orderBy('id')
            ->get();

        $this->assertCount(2, $lampiran);
        $this->assertSame('foto-rumah.jpg', $lampiran[0]->original_name);
        $this->assertSame('surat-kontrak.pdf', $lampiran[1]->original_name);
    }

    public function test_resident_update_deletes_household_lampiran(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        Storage::fake('local');

        $lampiran = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'lampiran',
            'file_path' => 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id.'/hapus.jpg',
            'original_name' => 'hapus-lampiran.jpg',
            'mime_type' => 'image/jpeg',
        ]);
        Storage::disk('local')->put($lampiran->file_path, 'dummy');

        $this->actingAs($staff)
            ->put(route('rt.residents.update', $active), array_merge(
                $this->residentUpdatePayload($household, $active),
                ['remove_household_document' => [$lampiran->id]]
            ))
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]));

        $this->assertDatabaseMissing('pendataan_documents', ['id' => $lampiran->id]);
        $this->assertFalse(Storage::disk('local')->exists($lampiran->file_path));
    }

    public function test_resident_update_shows_friendly_error_when_storage_not_writable(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        $relativePath = 'pendataan/rt-'.$household->rt_profile_id.'/household-'.$household->id;
        $blockedPath = storage_path('app/private/'.$relativePath);

        if (is_dir($blockedPath)) {
            Storage::disk('local')->deleteDirectory($relativePath);
        }

        if (! is_dir(dirname($blockedPath))) {
            mkdir(dirname($blockedPath), 0775, true);
        }

        file_put_contents($blockedPath, 'blocked');

        try {
            $this->actingAs($staff)
                ->from(route('rt.residents.edit', $active))
                ->put(route('rt.residents.update', $active), array_merge(
                    $this->residentUpdatePayload($household, $active),
                    ['document_identity' => UploadedFile::fake()->create('ktp-baru.jpg', 100, 'image/jpeg')]
                ))
                ->assertRedirect(route('rt.residents.edit', $active))
                ->assertSessionHasErrors('document_identity');

            $errors = session('errors')->get('document_identity');
            $this->assertStringContainsString('Folder penyimpanan server belum siap', $errors[0]);
        } finally {
            if (is_file($blockedPath)) {
                unlink($blockedPath);
            }
        }
    }

    public function test_resident_update_refreshes_last_updated_on_edit_page(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->put(route('rt.residents.update', $active), [
                'nik' => $active->nik,
                'name' => $active->name,
                'birth_place' => $active->birth_place,
                'birth_date' => $active->birth_date?->format('Y-m-d'),
                'gender' => $active->gender,
                'religion' => $active->religion,
                'occupation' => $active->occupation,
                'education' => $active->education,
                'marital_status' => $active->marital_status,
                'citizenship' => $active->citizenship,
                'phone' => $active->phone,
                'relationship_to_head' => 'Kepala Keluarga',
                'is_head_of_family' => '1',
                'whatsapp_notify' => '1',
                'family_card_number' => $household->family_card_number,
                'address' => $household->address,
                'house_number' => $household->house_number,
                'status_rumah_tinggal' => $household->status_rumah_tinggal,
                'suku' => $household->suku,
                'kondisi_rumah_milik' => $household->kondisi_rumah_milik,
            ])
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]));

        $active->refresh();
        $expectedTimestamp = collect([$active->updated_at, $household->fresh()->updated_at])
            ->filter()
            ->max()
            ->timezone('Asia/Jayapura')
            ->format('d/m/Y H:i');

        $this->actingAs($staff)
            ->get(route('rt.residents.edit', $active))
            ->assertOk()
            ->assertSee('Terakhir diperbarui: '.$expectedTimestamp, false);
    }

    public function test_resident_update_always_enables_whatsapp_notify(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();
        $active->update(['whatsapp_notify' => false]);

        $this->actingAs($staff)
            ->put(route('rt.residents.update', $active), array_merge(
                $this->residentUpdatePayload($household, $active),
                ['whatsapp_notify' => '0'],
            ))
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]));

        $this->assertTrue($active->fresh()->whatsapp_notify);
    }

    public function test_resident_update_persists_inline_household_fields(): void
    {
        [$staff, $household, $active] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->put(route('rt.residents.update', $active), [
                'nik' => $active->nik,
                'name' => $active->name,
                'birth_place' => $active->birth_place,
                'birth_date' => $active->birth_date?->format('Y-m-d'),
                'gender' => $active->gender,
                'religion' => $active->religion,
                'occupation' => $active->occupation,
                'education' => $active->education,
                'marital_status' => $active->marital_status,
                'citizenship' => $active->citizenship,
                'phone' => $active->phone,
                'relationship_to_head' => 'Kepala Keluarga',
                'is_head_of_family' => '1',
                'whatsapp_notify' => '1',
                'family_card_number' => $household->family_card_number,
                'address' => 'Jl. Baru RT 008 Diperbarui',
                'house_number' => '12B',
                'status_rumah_tinggal' => 'Milik sendiri',
                'suku' => 'Kamoro',
                'kondisi_rumah_milik' => 'layak',
            ])
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]));

        $household->refresh();
        $this->assertSame('Jl. Baru RT 008 Diperbarui', $household->address);
        $this->assertSame('12B', $household->house_number);
        $this->assertSame('Kamoro', $household->suku);
    }

    public function test_search_with_no_matches_shows_empty_state(): void
    {
        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['q' => 'tidak-ada-data-cocok', 'filter' => 'semua']))
            ->assertOk()
            ->assertSee('Tidak ada hasil');
    }

    public function test_index_shows_empty_rt_message_when_no_residents(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT',
            'email' => 'ketua-empty-rt@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['filter' => 'aktif']))
            ->assertOk()
            ->assertSee('Belum ada warga aktif')
            ->assertDontSee('Coba kata kunci lain');
    }

    public function test_stats_ignore_orphan_households_without_residents(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '077',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT',
        ]);

        Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101017777',
            'address' => 'Jl. Kosong',
            'pendataan_category' => '',
        ]);

        $stats = app(ResidentDataIndexService::class)->stats($profile);

        $this->assertSame(0, $stats['households']);
        $this->assertSame(0, $stats['residents_active']);
        $this->assertSame(0, $stats['residents_archived']);
    }

    public function test_household_query_excludes_empty_households_for_semua_filter(): void
    {
        $profile = RtProfile::create([
            'rt_number' => '078',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Inauga',
            'ketua_rt' => 'Ketua RT',
        ]);

        Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101018888',
            'address' => 'Jl. Kosong',
            'pendataan_category' => '',
        ]);

        $withMember = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101018899',
            'address' => 'Jl. Ada Warga',
            'pendataan_category' => '',
        ]);

        Resident::create([
            'household_id' => $withMember->id,
            'nik' => '3201010101010099',
            'name' => 'Warga Tersisa',
            'domicile_status' => DomicileStatus::Aktif,
            'is_head_of_family' => true,
        ]);

        $households = app(ResidentDataIndexService::class)
            ->buildHouseholdQuery($profile, 'semua', 'semua', '')
            ->get();

        $this->assertCount(1, $households);
        $this->assertSame($withMember->id, $households->first()->id);
    }

    public function test_search_by_resident_name(): void
    {
        [$staff, , , $archived] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['q' => 'Budi', 'filter' => 'semua']))
            ->assertOk()
            ->assertSee('Budi Aktif')
            ->assertDontSee('id="resident-row-'.$archived->id.'"', false)
            ->assertSee('Status: Semua status (pencarian)');
    }

    public function test_search_ignores_filter_status_when_query_present(): void
    {
        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['q' => 'Siti', 'filter' => 'aktif']))
            ->assertOk()
            ->assertSee('Siti Arsip')
            ->assertSee('Status: Semua status (pencarian)');
    }

    public function test_search_by_family_card_number(): void
    {
        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['q' => '3201010101010001', 'filter' => 'semua']))
            ->assertOk()
            ->assertSee('3201010101010001');
    }

    public function test_search_by_address_ignores_filter_status_when_query_present(): void
    {
        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.index', ['q' => 'Merpati', 'filter' => 'aktif']))
            ->assertOk()
            ->assertSee('3201010101010001')
            ->assertSee('Budi Aktif')
            ->assertSee('Status: Semua status (pencarian)');
    }

    public function test_legacy_residents_index_redirects_to_combined_page(): void
    {
        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.residents.index', ['filter' => 'arsip']))
            ->assertRedirect(route('rt.data-warga.index', ['filter' => 'arsip']));
    }

    public function test_legacy_households_index_redirects_to_combined_page(): void
    {
        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.households.index'))
            ->assertRedirect(route('rt.data-warga.index'));
    }

    public function test_create_registration_form_renders(): void
    {
        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.data-warga.create'))
            ->assertOk()
            ->assertSee('Daftar KK')
            ->assertSee('PDF/JPG/PNG, maks. 5 MB per berkas.', false)
            ->assertSee('Arsip dokumen keluarga.', false)
            ->assertSee('Referensi wajah surat online', false)
            ->assertDontSee('pendataan warga baru', false)
            ->assertSee('data-rt-registration-page', false)
            ->assertSee('lw-rt-reg-form', false)
            ->assertSee('lw-panel-form--labeled', false)
            ->assertSee('lw-panel-form-grid--2', false)
            ->assertSee('data-rt-registration-form', false)
            ->assertSee('lw-household-recap-fields--panel', false)
            ->assertSee('data-members-container', false)
            ->assertSee('rt-household-registration', false);
    }

    public function test_rt_registration_requires_member_nik(): void
    {
        Storage::fake('local');

        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->from(route('rt.data-warga.create'))
            ->post(route('rt.data-warga.store'), [
                'family_card_number' => '3201010101018888',
                'address' => 'Jl. Tanpa NIK',
                'status_rumah_tinggal' => 'Kontrak',
                'suku' => 'Mee',
                'phone' => '081234567801',
                'members' => [[
                    'name' => 'Warga Tanpa NIK',
                    'relationship' => 'Kepala Keluarga',
                    'birth_place' => 'Timika',
                    'birth_date' => '1990-01-01',
                    'gender' => 'Laki-laki',
                    'occupation' => 'Pegawai',
                    'education' => 'SMA/SMK',
                    'religion' => 'Islam',
                    'marital_status' => 'Kawin',
                    'citizenship' => 'WNI',
                ]],
                'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
                'document_ktp' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['members.0.nik']);
    }

    public function test_households_create_redirects_to_unified_form(): void
    {
        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->get(route('rt.households.create'))
            ->assertRedirect(route('rt.data-warga.create'));
    }

    public function test_rt_can_register_household_with_multiple_members(): void
    {
        Storage::fake('local');

        [$staff, $household] = array_slice($this->seedHouseholdWithMembers(), 0, 2);

        $payload = [
            'family_card_number' => '3201010101019999',
            'house_number' => '12',
            'address' => 'Jl. Cendrawasih RT 008',
            'status_rumah_tinggal' => 'Milik sendiri',
            'kondisi_rumah_milik' => 'layak',
            'suku' => 'Kamoro',
            'phone' => '081234567890',
            'whatsapp_notify' => '1',
            'members' => [
                [
                    'name' => 'Andi Kepala',
                    'nik' => '3201010101010002',
                    'relationship' => 'Kepala Keluarga',
                    'birth_place' => 'Timika',
                    'birth_date' => '1985-05-10',
                    'gender' => 'Laki-laki',
                    'occupation' => 'PNS',
                    'education' => 'S1',
                    'religion' => 'Kristen',
                    'marital_status' => 'Kawin',
                    'citizenship' => 'WNI',
                ],
                [
                    'name' => 'Rina Anggota',
                    'nik' => '3201010101010003',
                    'relationship' => 'Istri',
                    'birth_place' => 'Timika',
                    'birth_date' => '1988-08-20',
                    'gender' => 'Perempuan',
                    'occupation' => 'Mengurus rumah tangga',
                    'education' => 'SMA/SMK',
                    'religion' => 'Kristen',
                    'marital_status' => 'Kawin',
                    'citizenship' => 'WNI',
                ],
            ],
        ];

        $response = $this->actingAs($staff)
            ->post(route('rt.data-warga.store'), array_merge($payload, [
                'document_kk' => UploadedFile::fake()->create('kk-baru.pdf', 120, 'application/pdf'),
                'document_ktp' => UploadedFile::fake()->create('ktp-baru.jpg', 120, 'image/jpeg'),
            ]));

        $newHousehold = Household::where('family_card_number', '3201010101019999')->first();

        $this->assertNotNull($newHousehold);
        $response->assertRedirect(route('rt.data-warga.index', [
            'household' => $newHousehold->id,
            'filter' => 'aktif',
        ]));

        $this->assertDatabaseHas('households', [
            'id' => $newHousehold->id,
            'family_card_number' => '3201010101019999',
            'registration_type' => 'keluarga',
        ]);

        $residents = Resident::where('household_id', $newHousehold->id)->get();
        $this->assertCount(2, $residents);
        $this->assertSame(1, $residents->where('is_head_of_family', true)->count());
        $this->assertTrue($residents->firstWhere('name', 'Andi Kepala')?->is_head_of_family);
        $this->assertEquals(DomicileStatus::Aktif, $residents->first()->domicile_status);

        $this->assertDatabaseCount('pendataan_documents', 2);

        $head = $residents->firstWhere('is_head_of_family', true);

        $this->actingAs($staff)
            ->get(route('rt.residents.show', $head))
            ->assertOk()
            ->assertSee('Lampiran berkas')
            ->assertSee('lw-rt-doc-card-badge', false)
            ->assertSee('>KK<', false)
            ->assertSee('>KTP<', false);
    }

    public function test_rt_registration_sends_whatsapp_verified_notification(): void
    {
        Storage::fake('local');
        $this->fakeWahaWorking();

        [$staff] = $this->seedHouseholdWithMembers();

        $this->actingAs($staff)
            ->post(route('rt.data-warga.store'), [
                'family_card_number' => '3201010101018888',
                'house_number' => '15',
                'address' => 'Jl. Notif WA RT 008',
                'status_rumah_tinggal' => 'Milik sendiri',
                'kondisi_rumah_milik' => 'layak',
                'suku' => 'Mee',
                'phone' => '081234567801',
                'whatsapp_notify' => '1',
                'members' => [[
                    'name' => 'Warga Notif WA',
                    'nik' => '3201010101018888',
                    'relationship' => 'Kepala Keluarga',
                    'birth_place' => 'Timika',
                    'birth_date' => '1990-01-01',
                    'gender' => 'Laki-laki',
                    'occupation' => 'Pegawai',
                    'education' => 'SMA/SMK',
                    'religion' => 'Islam',
                    'marital_status' => 'Kawin',
                    'citizenship' => 'WNI',
                ]],
                'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
                'document_ktp' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $head = Resident::where('nik', '3201010101018888')->first();
        $this->assertNotNull($head);

        $log = NotificationLog::query()
            ->where('resident_id', $head->id)
            ->where('event', 'pendataan_registered_by_rt')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
        $this->assertNotSame('', trim($log->message ?? ''));
        $this->assertStringContainsString('dicatat', $log->message);
    }

    public function test_rt_add_member_sends_whatsapp_to_head(): void
    {
        $this->fakeWahaWorking();

        [$staff, $household, $head] = array_slice($this->seedHouseholdWithMembers(), 0, 3);
        $head->update(['phone' => '081234567899']);

        $this->actingAs($staff)
            ->post(route('rt.residents.store'), [
                'household_id' => $household->id,
                'name' => 'Anak Baru',
                'relationship_to_head' => 'Anak',
                'filter' => 'aktif',
            ])
            ->assertRedirect(route('rt.data-warga.index', [
                'filter' => 'aktif',
                'household' => $household->id,
            ]));

        $log = NotificationLog::query()
            ->where('resident_id', $head->id)
            ->where('event', 'pendataan_registered_by_rt')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
        $this->assertStringContainsString('Anak Baru', $log->message);
    }

    public function test_rt_rejects_duplicate_family_card_number(): void
    {
        Storage::fake('local');

        [$staff, $household] = array_slice($this->seedHouseholdWithMembers(), 0, 2);

        $this->actingAs($staff)
            ->from(route('rt.data-warga.create'))
            ->post(route('rt.data-warga.store'), [
                'family_card_number' => $household->family_card_number,
                'address' => 'Jl. Duplikat',
                'status_rumah_tinggal' => 'Kontrak',
                'suku' => 'Mee',
                'phone' => '081299988877',
                'members' => [[
                    'name' => 'Warga Duplikat',
                    'nik' => '3201010101017777',
                    'relationship' => 'Kepala Keluarga',
                    'birth_place' => 'Timika',
                    'birth_date' => '1990-01-01',
                    'gender' => 'Laki-laki',
                    'occupation' => 'Pegawai',
                    'education' => 'SMA/SMK',
                    'religion' => 'Islam',
                    'marital_status' => 'Kawin',
                    'citizenship' => 'WNI',
                ]],
                'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
                'document_ktp' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors([
                'family_card_number' => 'Nomor KK sudah terdaftar. Periksa kembali atau hubungi pengurus RT.',
            ]);
    }

    public function test_household_edit_page_uses_kk_table_layout(): void
    {
        [$staff, $household] = array_slice($this->seedHouseholdWithMembers(), 0, 2);

        $this->actingAs($staff)
            ->get(route('rt.households.edit', $household))
            ->assertOk()
            ->assertSee('lw-rt-household-edit-page', false)
            ->assertSee('lw-rt-resident-detail-table', false)
            ->assertSee('Kartu keluarga', false)
            ->assertSee('name="family_card_number"', false)
            ->assertSee('name="status_rumah_tinggal"', false)
            ->assertDontSee('name="suku"', false)
            ->assertSee('Terakhir diperbarui:', false);
    }

    public function test_household_update_without_suku_field_preserves_existing_suku(): void
    {
        [$staff, $household] = array_slice($this->seedHouseholdWithMembers(), 0, 2);

        $this->actingAs($staff)
            ->put(route('rt.households.update', $household), [
                'family_card_number' => $household->family_card_number,
                'house_number' => '12A',
                'address' => $household->address,
                'status_rumah_tinggal' => 'Milik sendiri',
                'kondisi_rumah_milik' => 'layak',
            ])
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]));

        $household->refresh();
        $this->assertSame('Amungme', $household->suku);
        $this->assertSame('12A', $household->house_number);
    }

    public function test_archived_resident_show_displays_departure_notes(): void
    {
        [$staff, , , $archived] = $this->seedHouseholdWithMembers();
        $archived->update([
            'domicile_status' => \App\Enums\DomicileStatus::PindahKeluar,
            'departure_reason' => 'pindah',
            'departed_at' => '2026-05-01',
            'departure_notes' => 'Pindah RT lain',
            'departed_by' => $staff->id,
        ]);

        $response = $this->actingAs($staff)
            ->get(route('rt.residents.show', [
                'resident' => $archived,
                'filter' => 'arsip',
                'household' => $archived->household_id,
            ]));

        $content = $response->getContent();

        $response->assertOk()
            ->assertSee('Pindah RT lain', false)
            ->assertSee('Pindah keluar', false)
            ->assertDontSee('Kartu keluarga', false)
            ->assertSee('Daftar Anggota Keluarga', false)
            ->assertSee('Identitas warga', false)
            ->assertDontSee('data-delete-action="'.route('rt.residents.destroy', $archived).'"', false);

        $this->assertLessThan(
            strpos($content, 'Identitas warga'),
            strpos($content, 'Daftar Anggota Keluarga'),
            'Daftar Anggota Keluarga harus muncul sebelum Identitas warga'
        );
    }
}
