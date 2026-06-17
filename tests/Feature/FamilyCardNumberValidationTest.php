<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FamilyCardNumberValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createRt(): RtProfile
    {
        return RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);
    }

    private function seedActiveHousehold(RtProfile $rt, string $kk = '3201010101010099'): Resident
    {
        $household = Household::create([
            'rt_profile_id' => $rt->id,
            'family_card_number' => $kk,
            'address' => 'Jl. Uji No. 1',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Amungme',
        ]);

        return Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010044',
            'name' => 'Warga Uji KK',
            'phone' => '081234567890',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-01',
            'gender' => 'Laki-laki',
            'relationship_to_head' => 'Kepala Keluarga',
            'is_head_of_family' => true,
            'occupation' => 'Pegawai',
            'education' => 'S1',
            'religion' => 'Islam',
            'marital_status' => 'Kawin',
            'citizenship' => 'WNI',
            'domicile_status' => DomicileStatus::Aktif,
        ]);
    }

    public function test_pendataan_ulang_document_submit_keeps_existing_family_card_number(): void
    {
        $rt = $this->createRt();
        $head = $this->seedActiveHousehold($rt, '3201010101010001');

        $this->post(route('services.pendataan-ulang.verify'), [
            'rt_profile_id' => $rt->id,
            'nik' => $head->nik,
            'phone' => $head->phone,
        ])->assertRedirect(route('services.pendataan-ulang'));

        $this->withSession(['pendataan_ulang_resident_id' => $head->id])
            ->post(route('services.pendataan-ulang.store'), [
                'family_card_number' => '3201010101010099',
                'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
                'members' => [[
                    'resident_id' => $head->id,
                    'document_id' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
                ]],
            ])
            ->assertRedirect(route('services.pendataan-ulang.success'));

        $this->assertSame('3201010101010001', $head->household->fresh()->family_card_number);
    }
}
