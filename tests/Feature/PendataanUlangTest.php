<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PendataanUlangTest extends TestCase
{
    use RefreshDatabase;

    private function createRt(): RtProfile
    {
        return RtProfile::create([
            'rt_number' => '002',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 002',
        ]);
    }

    private function seedActiveHousehold(RtProfile $rt): Resident
    {
        $household = Household::create([
            'rt_profile_id' => $rt->id,
            'family_card_number' => '3201010101010055',
            'address' => 'Jl. Lama No. 1',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        return Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010055',
            'name' => 'Kepala Ulang',
            'phone' => '081299988899',
            'birth_place' => 'Timika',
            'birth_date' => '1988-08-08',
            'gender' => 'Laki-laki',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'occupation' => 'Petani/Pekebun',
            'education' => 'SMA/SMK',
            'religion' => 'Kristen',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'domicile_status' => DomicileStatus::Aktif,
        ]);
    }

    /** @return array<string, mixed> */
    private function documentPayload(Resident $head): array
    {
        return [
            'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
            'whatsapp_notify' => '1',
            'members' => [[
                'resident_id' => $head->id,
                'document_id' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            ]],
        ];
    }

    public function test_old_pendataan_route_redirects_to_pendataan_warga_form(): void
    {
        $this->get(route('services.pendataan'))
            ->assertRedirect(route('services.pendataan-warga'));
    }

    public function test_pembaruan_redirects_to_pendataan_ulang(): void
    {
        $this->get(route('services.pembaruan'))
            ->assertRedirect('/layanan/pendataan-ulang');
    }

    public function test_pengaduan_redirects_to_kontak(): void
    {
        $this->get(route('services.pengaduan'))
            ->assertRedirect('/kontak?category=pengaduan_lingkungan');
    }

    public function test_verify_and_submit_pendataan_ulang_documents_only(): void
    {
        $rt = $this->createRt();
        $head = $this->seedActiveHousehold($rt);
        $originalAddress = $head->household->address;

        $this->post(route('services.pendataan-ulang.verify'), [
            'rt_profile_id' => $rt->id,
            'nik' => $head->nik,
            'phone' => $head->phone,
        ])->assertRedirect(route('services.pendataan-ulang'));

        $this->withSession(['pendataan_ulang_resident_id' => $head->id])
            ->post(route('services.pendataan-ulang.store'), $this->documentPayload($head))
            ->assertRedirect(route('services.pendataan-ulang.success'));

        $head->refresh();
        $this->assertSame(DomicileStatus::MenungguVerifikasi, $head->domicile_status);
        $this->assertSame('pendataan_ulang', $head->household->pendataan_category);
        $this->assertSame($originalAddress, $head->household->address);
        $this->assertSame('Petani/Pekebun', $head->occupation);
        $this->assertDatabaseHas('pendataan_documents', [
            'household_id' => $head->household_id,
            'document_type' => 'kk',
        ]);
        $this->assertDatabaseHas('pendataan_documents', [
            'household_id' => $head->household_id,
            'document_type' => 'ktp_a0',
        ]);
    }

    public function test_pendataan_ulang_page_shows_unified_phone_label(): void
    {
        $this->get(route('services.pendataan-ulang'))
            ->assertOk()
            ->assertSee('Nomor HP/ WhatsApp', false)
            ->assertDontSee('No. HP terdaftar', false);
    }

    public function test_pendataan_ulang_page_shows_upload_only_after_verify(): void
    {
        $rt = $this->createRt();
        $head = $this->seedActiveHousehold($rt);

        $this->post(route('services.pendataan-ulang.verify'), [
            'rt_profile_id' => $rt->id,
            'nik' => $head->nik,
            'phone' => $head->phone,
        ]);

        $this->withSession(['pendataan_ulang_resident_id' => $head->id])
            ->get(route('services.pendataan-ulang'))
            ->assertOk()
            ->assertSee('Scan/foto Kartu Keluarga (KK)', false)
            ->assertSee('Kepala Ulang', false)
            ->assertDontSee('name="family_card_number"', false)
            ->assertDontSee('data-add-member-btn', false);
    }

    public function test_pendataan_ulang_submit_enables_whatsapp_notify_for_all_members(): void
    {
        Storage::fake('local');

        $rt = $this->createRt();
        $head = $this->seedActiveHousehold($rt);

        $member = Resident::create([
            'household_id' => $head->household_id,
            'nik' => '3201010101010056',
            'name' => 'Anggota Ulang',
            'birth_place' => 'Timika',
            'birth_date' => '2010-01-01',
            'gender' => 'Perempuan',
            'is_head_of_family' => false,
            'relationship_to_head' => 'Anak',
            'occupation' => 'Pelajar/Mahasiswa',
            'education' => 'SD',
            'religion' => 'Kristen',
            'marital_status' => 'Belum kawin',
            'citizenship' => 'WNI',
            'domicile_status' => DomicileStatus::Aktif,
            'whatsapp_notify' => false,
        ]);

        $this->post(route('services.pendataan-ulang.verify'), [
            'rt_profile_id' => $rt->id,
            'nik' => $head->nik,
            'phone' => $head->phone,
        ])->assertRedirect(route('services.pendataan-ulang'));

        $this->withSession(['pendataan_ulang_resident_id' => $head->id])
            ->post(route('services.pendataan-ulang.store'), [
                'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
                'whatsapp_notify' => '1',
                'members' => [
                    [
                        'resident_id' => $head->id,
                        'document_id' => UploadedFile::fake()->create('ktp-head.pdf', 100, 'application/pdf'),
                    ],
                    [
                        'resident_id' => $member->id,
                        'document_id' => UploadedFile::fake()->create('kia-member.pdf', 100, 'application/pdf'),
                    ],
                ],
            ])
            ->assertRedirect(route('services.pendataan-ulang.success'));

        $this->assertTrue($head->fresh()->whatsapp_notify);
        $this->assertTrue($member->fresh()->whatsapp_notify);
    }

    public function test_layanan_index_shows_pendataan_ulang_and_pendataan_warga(): void
    {
        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Pendataan ulang', false)
            ->assertSee('Pendataan warga', false)
            ->assertDontSee('Pengaduan lingkungan', false);
    }

    public function test_contact_shows_environment_fields_for_category(): void
    {
        $this->get(route('contact.create', ['category' => 'pengaduan_lingkungan']))
            ->assertOk()
            ->assertSee('id="environment-fields"', false)
            ->assertSee('incident_type', false);
    }
}
