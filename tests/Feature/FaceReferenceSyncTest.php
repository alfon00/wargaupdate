<?php

namespace Tests\Feature;

use App\Enums\DomicileStatus;
use App\Enums\UserRole;
use App\Models\Household;
use App\Models\PendataanDocument;
use App\Models\Resident;
use App\Models\ResidentFaceReference;
use App\Models\RtProfile;
use App\Models\User;
use App\Services\FaceDescriptorExtractor;
use App\Services\ResidentFaceReferenceService;
use App\Support\SuratFaceReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FaceReferenceSyncTest extends TestCase
{
    use RefreshDatabase;

    private function createRtStaff(): array
    {
        $profile = RtProfile::create([
            'rt_number' => '010',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 010',
        ]);

        $staff = User::create([
            'name' => 'Ketua RT',
            'email' => 'face-sync@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::KetuaRt,
            'rt_profile_id' => $profile->id,
        ]);

        return [$profile, $staff];
    }

    private function seedHouseholdWithDocuments(RtProfile $profile): Household
    {
        Storage::fake('local');

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010077',
            'address' => 'Jl. Wajah No. 1',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010077',
            'name' => 'Kepala Wajah',
            'phone' => '081234567890',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-01',
            'gender' => 'Laki-laki',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        Storage::disk('local')->put('pendataan/test/kk.jpg', 'fake-image');
        Storage::disk('local')->put('pendataan/test/ktp.jpg', 'fake-image');

        PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'kk',
            'file_path' => 'pendataan/test/kk.jpg',
            'original_name' => 'kk.jpg',
            'mime_type' => 'image/jpeg',
            'face_extraction_error' => 'tidak ada wajah terdeteksi pada dokumen',
        ]);

        PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'ktp_kepala',
            'file_path' => 'pendataan/test/ktp.jpg',
            'original_name' => 'ktp.jpg',
            'mime_type' => 'image/jpeg',
            'face_extraction_error' => 'Node.js tidak tersedia di server',
        ]);

        return $household;
    }

    /** @return array<int, float> */
    private function sampleDescriptor(): array
    {
        return array_map(static fn (int $i) => round($i / 128, 6), range(0, 127));
    }

    public function test_readiness_includes_stored_extraction_errors_from_ktp_only(): void
    {
        [$profile] = $this->createRtStaff();
        $household = $this->seedHouseholdWithDocuments($profile);

        $readiness = app(ResidentFaceReferenceService::class)
            ->readinessForHousehold($household);

        $this->assertSame(SuratFaceReadiness::STATUS_EXTRACTION_FAILED, $readiness->status);
        $this->assertStringContainsString('Node.js tidak tersedia', $readiness->detail ?? '');
        $this->assertStringNotContainsString('tidak ada wajah terdeteksi', $readiness->detail ?? '');
    }

    public function test_kk_only_does_not_satisfy_identity_documents(): void
    {
        [$profile] = $this->createRtStaff();
        $household = $this->seedHouseholdWithDocuments($profile);
        PendataanDocument::query()->where('document_type', 'ktp_kepala')->delete();

        $head = $household->residents()->first();
        $service = app(ResidentFaceReferenceService::class);

        $this->assertFalse($service->hasIdentityDocuments($head));
        $this->assertSame(
            SuratFaceReadiness::STATUS_MISSING_DOCUMENTS,
            $service->readinessForResident($head)->status,
        );
    }

    public function test_rt_can_sync_face_references_for_own_household(): void
    {
        [$profile, $staff] = $this->createRtStaff();
        $household = $this->seedHouseholdWithDocuments($profile);

        $descriptor = $this->sampleDescriptor();

        $this->mock(FaceDescriptorExtractor::class, function ($mock) use ($descriptor) {
            $mock->shouldReceive('extractFromStoragePath')
                ->once()
                ->andReturn([
                    ['descriptor' => $descriptor, 'face_index' => 0],
                ]);
            $mock->shouldReceive('getLastError')->andReturn(null);
        });

        $this->actingAs($staff)
            ->post(route('rt.households.sync-face-references', $household))
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]))
            ->assertSessionHas('success');

        $this->assertSame(1, ResidentFaceReference::query()->count());
        $this->assertNull(
            PendataanDocument::query()->where('document_type', 'ktp_kepala')->value('face_extraction_error')
        );
        $this->assertSame(
            'tidak ada wajah terdeteksi pada dokumen',
            PendataanDocument::query()->where('document_type', 'kk')->value('face_extraction_error')
        );
    }

    public function test_non_head_member_uses_own_ktp_reference(): void
    {
        [$profile] = $this->createRtStaff();

        Storage::fake('local');
        Storage::disk('local')->put('pendataan/test/ktp-member.jpg', 'fake-image');

        $household = Household::create([
            'rt_profile_id' => $profile->id,
            'family_card_number' => '3201010101010088',
            'address' => 'Jl. Wajah No. 2',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010088',
            'name' => 'Kepala',
            'phone' => '081234567891',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-01',
            'gender' => 'Laki-laki',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $member = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010089',
            'name' => 'Anggota',
            'phone' => '081234567892',
            'birth_place' => 'Timika',
            'birth_date' => '1995-01-01',
            'gender' => 'Perempuan',
            'is_head_of_family' => false,
            'relationship_to_head' => 'Anak',
            'domicile_status' => DomicileStatus::Aktif,
        ]);

        $document = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => 'ktp_a1',
            'file_path' => 'pendataan/test/ktp-member.jpg',
            'original_name' => 'ktp-member.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $descriptor = $this->sampleDescriptor();

        $this->mock(FaceDescriptorExtractor::class, function ($mock) use ($descriptor) {
            $mock->shouldReceive('extractFromStoragePath')
                ->once()
                ->andReturn([
                    ['descriptor' => $descriptor, 'face_index' => 0],
                ]);
            $mock->shouldReceive('getLastError')->andReturn(null);
        });

        $service = app(ResidentFaceReferenceService::class);
        $result = $service->syncFromDocument($document);

        $this->assertTrue($result['ok']);
        $this->assertSame(1, ResidentFaceReference::query()->count());
        $this->assertSame($member->id, ResidentFaceReference::query()->value('resident_id'));
        $this->assertSame('ktp', ResidentFaceReference::query()->value('source'));
        $this->assertTrue($service->readinessForResident($member)->canVerify);
    }

    public function test_sync_route_shows_warning_when_extraction_still_fails(): void
    {
        [$profile, $staff] = $this->createRtStaff();
        $household = $this->seedHouseholdWithDocuments($profile);

        $this->mock(FaceDescriptorExtractor::class, function ($mock) {
            $mock->shouldReceive('extractFromStoragePath')->andReturn([]);
            $mock->shouldReceive('getLastError')->andReturn('tidak ada wajah terdeteksi pada dokumen');
        });

        $this->actingAs($staff)
            ->post(route('rt.households.sync-face-references', $household))
            ->assertRedirect(route('rt.data-warga.index', ['household' => $household->id]))
            ->assertSessionHas('face_sync_warning');
    }
}
