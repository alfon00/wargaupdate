<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Models\Household;
use App\Models\Resident;
use App\Models\RtProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PendataanHouseholdFieldsTest extends TestCase
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
            'address' => 'Jl. Rekap No. 1',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        return Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010055',
            'name' => 'Kepala Rekap',
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
    private function basePayload(Resident $head): array
    {
        return [
            'whatsapp_notify' => '1',
            'members' => [[
                'resident_id' => $head->id,
                'document_id' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            ]],
            'document_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
        ];
    }

    private function submitPendataanUlang(RtProfile $rt, Resident $head, array $overrides = [])
    {
        $this->post(route('services.pendataan-ulang.verify'), [
            'rt_profile_id' => $rt->id,
            'nik' => $head->nik,
            'phone' => $head->phone,
        ])->assertRedirect(route('services.pendataan-ulang'));

        return $this->withSession(['pendataan_ulang_resident_id' => $head->id])
            ->post(route('services.pendataan-ulang.store'), [
                ...$this->basePayload($head),
                ...$overrides,
            ]);
    }

    public function test_pendataan_ulang_preserves_household_recap_fields(): void
    {
        $rt = $this->createRt();
        $head = $this->seedActiveHousehold($rt);

        $this->submitPendataanUlang($rt, $head)
            ->assertRedirect(route('services.pendataan-ulang.success'));

        $household = $head->household->fresh();
        $this->assertSame('Kontrak', $household->status_rumah_tinggal);
        $this->assertSame('Mee', $household->suku);
        $this->assertSame('Jl. Rekap No. 1', $household->address);
    }

    public function test_pendataan_ulang_accepts_documents_up_to_five_megabytes(): void
    {
        $rt = $this->createRt();
        $head = $this->seedActiveHousehold($rt);

        $this->submitPendataanUlang($rt, $head, [
            'document_kk' => UploadedFile::fake()->create('kk.pdf', 5120, 'application/pdf'),
            'members' => [[
                'resident_id' => $head->id,
                'document_id' => UploadedFile::fake()->create('ktp.pdf', 5120, 'application/pdf'),
            ]],
        ])->assertRedirect(route('services.pendataan-ulang.success'));

        $this->assertDatabaseHas('pendataan_documents', [
            'household_id' => $head->household_id,
            'document_type' => 'kk',
        ]);
    }

    public function test_pendataan_ulang_rejects_oversized_document_with_indonesian_message(): void
    {
        app()->setLocale('id');
        $rt = $this->createRt();
        $head = $this->seedActiveHousehold($rt);

        $this->post(route('services.pendataan-ulang.verify'), [
            'rt_profile_id' => $rt->id,
            'nik' => $head->nik,
            'phone' => $head->phone,
        ])->assertRedirect(route('services.pendataan-ulang'));

        $response = $this->from(route('services.pendataan-ulang'))
            ->withSession(['pendataan_ulang_resident_id' => $head->id])
            ->post(route('services.pendataan-ulang.store'), [
                ...$this->basePayload($head),
                'document_kk' => UploadedFile::fake()->create('kk.pdf', 6144, 'application/pdf'),
            ]);

        $response->assertSessionHasErrors('document_kk');
        $errors = session('errors')->get('document_kk');
        $this->assertNotEmpty($errors);
        $this->assertStringNotContainsString('validation.uploaded', (string) $errors[0]);
        $this->assertStringContainsString('5 MB', (string) $errors[0]);
    }
}
