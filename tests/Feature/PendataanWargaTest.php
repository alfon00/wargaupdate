<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Household;
use App\Models\NotificationLog;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PendataanWargaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'waha.api_key' => 'test-waha-key',
            'waha.base_url' => 'http://waha:3000',
            'waha.session' => 'default',
        ]);
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

    private function createRt(string $rtNumber = '010'): RtProfile
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

    /** @return array{0: RtProfile, 1: User} */
    private function createRtWithStaff(string $rtNumber = '011'): array
    {
        $profile = $this->createRt($rtNumber);

        $staff = User::create([
            'name' => 'Ketua RT '.$rtNumber,
            'email' => 'ketua-rt-'.$rtNumber.'@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        return [$profile, $staff];
    }

    /** @return array<int, float> */
    private function sampleFaceDescriptor(): array
    {
        return array_map(static fn (int $i) => round($i / 128, 6), range(0, 127));
    }

    private function sampleSelfieDataUri(): string
    {
        return 'data:image/jpeg;base64,'.base64_encode(str_repeat('0', 1200));
    }

    /** @return array<string, mixed> */
    private function submitPayload(RtProfile $rt, string $familyCard = '3201010101018888', string $headNik = '3201010101018888'): array
    {
        $descriptor = $this->sampleFaceDescriptor();

        return [
            'rt_profile_id' => $rt->id,
            'family_card_number' => $familyCard,
            'house_number' => '12',
            'address' => 'Jl. Cendrawasih RT 010',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
            'phone' => '081299988877',
            'whatsapp_notify' => '1',
            'members' => [
                [
                    'name' => 'Andi Baru',
                    'nik' => $headNik,
                    'relationship' => 'Kepala Keluarga',
                    'birth_place' => 'Timika',
                    'birth_date' => '1985-05-10',
                    'gender' => 'Laki-laki',
                    'occupation' => 'PNS',
                    'education' => 'S1',
                    'religion' => 'Kristen',
                    'marital_status' => 'Kawin',
                    'citizenship' => 'WNI',
                    'document_id' => UploadedFile::fake()->create('ktp-kepala.pdf', 100, 'application/pdf'),
                ],
                [
                    'name' => 'Rina Baru',
                    'nik' => '3201010101018889',
                    'relationship' => 'Istri',
                    'birth_place' => 'Timika',
                    'birth_date' => '2015-03-15',
                    'gender' => 'Perempuan',
                    'occupation' => 'Pelajar/Mahasiswa',
                    'education' => 'SD',
                    'religion' => 'Kristen',
                    'marital_status' => 'Belum Kawin',
                    'citizenship' => 'WNI',
                    'document_id' => UploadedFile::fake()->create('kia-anak.pdf', 100, 'application/pdf'),
                ],
            ],
            'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
            'head_face_descriptor' => $descriptor,
            'head_selfie_data' => $this->sampleSelfieDataUri(),
        ];
    }

    public function test_pendataan_route_redirects_to_new_form(): void
    {
        $this->get(route('services.pendataan'))
            ->assertRedirect(route('services.pendataan-warga'));
    }

    public function test_submit_creates_household_pending_verification(): void
    {
        Storage::fake('local');

        $rt = $this->createRt();

        $this->post(route('services.pendataan-warga.store'), $this->submitPayload($rt))
            ->assertRedirect(route('services.pendataan-warga.success'));

        $household = Household::where('family_card_number', '3201010101018888')->first();
        $this->assertNotNull($household);
        $this->assertSame('menunggu_verifikasi', $household->status);
        $this->assertSame('warga_baru', $household->pendataan_category);
        $this->assertSame('keluarga', $household->registration_type);

        $head = $household->residents()->where('is_head_of_family', true)->first();
        $this->assertNotNull($head);
        $this->assertSame(DomicileStatus::MenungguVerifikasi, $head->domicile_status);
        $this->assertSame('Andi Baru', $head->name);

        $this->assertDatabaseHas('pendataan_documents', [
            'household_id' => $household->id,
            'document_type' => 'kk',
        ]);
        $this->assertDatabaseHas('pendataan_documents', [
            'household_id' => $household->id,
            'document_type' => 'ktp_a0',
        ]);
        $this->assertDatabaseHas('pendataan_documents', [
            'household_id' => $household->id,
            'document_type' => 'kia_a1',
        ]);
        $this->assertDatabaseHas('pendataan_documents', [
            'household_id' => $household->id,
            'document_type' => 'selfie_kepala',
        ]);
    }

    public function test_rejects_duplicate_nik_or_family_card(): void
    {
        Storage::fake('local');

        $rt = $this->createRt('012');
        $existing = Household::create([
            'rt_profile_id' => $rt->id,
            'family_card_number' => '3201010101017777',
            'address' => 'Jl. Lama',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        Resident::create([
            'household_id' => $existing->id,
            'nik' => '3201010101017777',
            'name' => 'Warga Lama',
            'phone' => '081200000012',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-01',
            'gender' => 'Laki-laki',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'occupation' => 'Pegawai',
            'education' => 'SMA/SMK',
            'religion' => 'Islam',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $this->from(route('services.pendataan-warga'))
            ->post(route('services.pendataan-warga.store'), $this->submitPayload($rt, '3201010101017777', '3201010101017777'))
            ->assertSessionHasErrors(['family_card_number']);

        $this->from(route('services.pendataan-warga'))
            ->post(route('services.pendataan-warga.store'), $this->submitPayload($rt, '3201010101016666', '3201010101017777'))
            ->assertSessionHasErrors(['members.0.nik']);
    }

    public function test_rejects_duplicate_nik_among_members(): void
    {
        Storage::fake('local');

        $rt = $this->createRt('014');
        $payload = $this->submitPayload($rt, '3201010101014444', '3201010101014444');
        $payload['members'][1]['nik'] = '3201010101014444';

        $response = $this->from(route('services.pendataan-warga'))
            ->post(route('services.pendataan-warga.store'), $payload);

        $response->assertSessionHasErrors(['members.1.nik']);
        $response->assertSessionMissing('pendataan_warga_success');

        $errorMessage = session('errors')->get('members.1.nik')[0] ?? '';
        $this->assertStringContainsString('harus berbeda', $errorMessage);
        $this->assertStringNotContainsString('validation.distinct', $errorMessage);

        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('data-validation-errors', false)
            ->assertDontSee('validation.distinct', false);
    }

    public function test_appears_on_rt_pendataan_queue(): void
    {
        Storage::fake('local');

        [$rt, $staff] = $this->createRtWithStaff('013');

        $this->post(route('services.pendataan-warga.store'), $this->submitPayload($rt, '3201010101015555', '3201010101015555'))
            ->assertRedirect(route('services.pendataan-warga.success'));

        $this->actingAs($staff)
            ->get(route('rt.pendataan.index'))
            ->assertOk()
            ->assertSee('Andi Baru');
    }

    public function test_layanan_index_shows_pendataan_warga_card(): void
    {
        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Pendataan warga', false)
            ->assertSee(route('services.pendataan-warga'), false);
    }

    public function test_pendataan_warga_form_page_loads(): void
    {
        $this->get(route('services.pendataan-warga'))
            ->assertOk()
            ->assertSee('Pendataan warga', false)
            ->assertSee('Nomor HP/ WhatsApp', false)
            ->assertSee('Alamat tempat tinggal', false)
            ->assertSee('sesuai alamat pada Kartu Keluarga atau KTP/KIA', false)
            ->assertSee('Lengkapi data keluarga sesuai dokumen KK dan KTP/KIA', false)
            ->assertSee('data-pendataan-warga-page', false)
            ->assertSee('data-household-registration-form', false)
            ->assertSee('data-include-member-documents="1"', false)
            ->assertSee('Verifikasi wajah kepala keluarga', false)
            ->assertSee('Ambil foto selfie kepala keluarga langsung di kamera', false)
            ->assertSee('id="face-switch-button"', false)
            ->assertSee('Ganti kamera', false)
            ->assertDontSee('dibandingkan dengan foto pada KTP/KIA', false)
            ->assertSee('lw-face-capture', false)
            ->assertSee('data-household-recap-fields', false)
            ->assertSee('name="suku"', false)
            ->assertSee('name="status_rumah_tinggal"', false)
            ->assertSee('id="field-kondisi-rumah"', false)
            ->assertSee('lw-is-hidden', false)
            ->assertSee('type="hidden" name="whatsapp_notify"', false)
            ->assertSee('checked disabled', false)
            ->assertDontSee('Alamat domisili', false)
            ->assertDontSee('name="document_ktp"', false)
            ->assertDontSee('Scan/foto KTP kepala KK', false);
    }

    public function test_submit_requires_face_verification_fields(): void
    {
        Storage::fake('local');

        $rt = $this->createRt('021');
        $payload = $this->submitPayload($rt, '3201010101010210', '3201010101010210');
        unset($payload['head_face_descriptor'], $payload['head_selfie_data']);

        $this->from(route('services.pendataan-warga'))
            ->post(route('services.pendataan-warga.store'), $payload)
            ->assertSessionHasErrors(['head_face_descriptor', 'head_selfie_data']);
    }

    public function test_submit_accepts_selfie_without_ktp_descriptor_match(): void
    {
        Storage::fake('local');

        $rt = $this->createRt('022');
        $payload = $this->submitPayload($rt, '3201010101010220', '3201010101010220');
        $payload['head_face_descriptor'] = array_map(
            static fn (int $i) => round(($i + 64) / 128, 6),
            range(0, 127),
        );

        $this->from(route('services.pendataan-warga'))
            ->post(route('services.pendataan-warga.store'), $payload)
            ->assertRedirect(route('services.pendataan-warga.success'))
            ->assertSessionHasNoErrors();
    }

    public function test_pendataan_warga_milik_sendiri_requires_kondisi_rumah_milik(): void
    {
        Storage::fake('local');

        $rt = $this->createRt('019');
        $payload = $this->submitPayload($rt, '3201010101010199', '3201010101010199');
        $payload['status_rumah_tinggal'] = 'Milik sendiri';
        unset($payload['kondisi_rumah_milik']);

        $this->from(route('services.pendataan-warga'))
            ->post(route('services.pendataan-warga.store'), $payload)
            ->assertSessionHasErrors(['kondisi_rumah_milik']);
    }

    public function test_pendataan_warga_kontrak_does_not_require_kondisi_rumah_milik(): void
    {
        Storage::fake('local');

        $rt = $this->createRt('020');

        $this->post(route('services.pendataan-warga.store'), $this->submitPayload($rt, '3201010101010200', '3201010101010200'))
            ->assertRedirect(route('services.pendataan-warga.success'));

        $household = Household::where('family_card_number', '3201010101010200')->first();
        $this->assertNotNull($household);
        $this->assertSame('Kontrak', $household->status_rumah_tinggal);
        $this->assertNull($household->kondisi_rumah_milik);
    }

    public function test_warga_baru_submit_enables_whatsapp_notify_for_all_members(): void
    {
        Storage::fake('local');

        $rt = $this->createRt('018');

        $this->post(route('services.pendataan-warga.store'), $this->submitPayload($rt, '3201010101014444', '3201010101014444'))
            ->assertRedirect(route('services.pendataan-warga.success'));

        $members = Resident::whereHas('household', fn ($q) => $q->where('family_card_number', '3201010101014444'))->get();
        $this->assertCount(2, $members);
        $this->assertTrue($members->every(fn (Resident $resident) => $resident->whatsapp_notify));
    }

    public function test_warga_baru_submitted_sends_whatsapp_log(): void
    {
        Storage::fake('local');
        $this->fakeWahaWorking();

        $rt = $this->createRt('015');

        $this->post(route('services.pendataan-warga.store'), $this->submitPayload($rt, '3201010101013333', '3201010101013333'))
            ->assertRedirect(route('services.pendataan-warga.success'));

        $head = Resident::where('nik', '3201010101013333')->first();
        $this->assertNotNull($head);

        $log = NotificationLog::query()
            ->where('resident_id', $head->id)
            ->where('event', 'pendataan_submitted')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('sent', $log->status);
    }

    public function test_warga_baru_reject_whatsapp_uses_pendataan_warga_url(): void
    {
        Storage::fake('local');
        $this->fakeWahaWorking();

        [$rt, $staff] = $this->createRtWithStaff('016');

        $this->post(route('services.pendataan-warga.store'), $this->submitPayload($rt, '3201010101012222', '3201010101012222'))
            ->assertRedirect(route('services.pendataan-warga.success'));

        $head = Resident::where('nik', '3201010101012222')->first();
        $this->assertNotNull($head);

        $this->actingAs($staff)
            ->post(route('rt.pendataan.reject', $head), [
                'rejection_notes' => 'Berkas tidak lengkap',
            ])
            ->assertRedirect(route('rt.pendataan.index'))
            ->assertSessionHas('success', 'Pendataan warga ditolak. Warga menerima notifikasi WhatsApp.');

        $log = NotificationLog::query()
            ->where('resident_id', $head->id)
            ->where('event', 'pendataan_rejected')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('/layanan/pendataan-warga', $log->message);
        $this->assertStringNotContainsString('/layanan/pendataan-ulang', $log->message);
    }

    public function test_warga_baru_request_completion_whatsapp_uses_pendataan_warga_url(): void
    {
        Storage::fake('local');
        $this->fakeWahaWorking();

        [$rt, $staff] = $this->createRtWithStaff('017');

        $this->post(route('services.pendataan-warga.store'), $this->submitPayload($rt, '3201010101011111', '3201010101011111'))
            ->assertRedirect(route('services.pendataan-warga.success'));

        $head = Resident::where('nik', '3201010101011111')->first();
        $this->assertNotNull($head);

        $this->actingAs($staff)
            ->post(route('rt.pendataan.request-completion', $head), [
                'verification_notes' => 'Unggah ulang scan KK',
            ])
            ->assertRedirect(route('rt.pendataan.index'));

        $log = NotificationLog::query()
            ->where('resident_id', $head->id)
            ->where('event', 'pendataan_incomplete')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('/layanan/pendataan-warga', $log->message);
        $this->assertStringNotContainsString('/layanan/pendataan-ulang', $log->message);
    }

    public function test_warga_baru_show_uses_category_label_in_confirm(): void
    {
        Storage::fake('local');

        [$rt, $staff] = $this->createRtWithStaff('018');

        $this->post(route('services.pendataan-warga.store'), $this->submitPayload($rt, '3201010101010001', '3201010101010001'))
            ->assertRedirect(route('services.pendataan-warga.success'));

        $head = Resident::where('nik', '3201010101010001')->first();
        $this->assertNotNull($head);

        NotificationLog::create([
            'resident_id' => $head->id,
            'phone' => $head->phone,
            'event' => 'pendataan_submitted',
            'message' => 'Test',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('rt.pendataan.show', $head))
            ->assertOk()
            ->assertSee('Tolak Warga baru ini?', false)
            ->assertSee('Pengajuan diterima', false)
            ->assertDontSee('pendataan_submitted', false)
            ->assertDontSee('Tolak pendataan ulang ini?', false);
    }
}
