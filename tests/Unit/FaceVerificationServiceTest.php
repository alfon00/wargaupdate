<?php

namespace Tests\Unit;

use App\Models\Household;
use App\Models\Resident;
use App\Models\ResidentFaceReference;
use App\Models\RtProfile;
use App\Services\FaceVerificationService;
use App\Services\ResidentFaceReferenceService;
use App\Support\SuratFaceReadiness;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FaceVerificationServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_euclidean_distance_is_zero_for_identical_descriptors(): void
    {
        $service = app(FaceVerificationService::class);
        $descriptor = array_fill(0, 128, 0.42);

        $this->assertSame(0.0, $service->euclideanDistance($descriptor, $descriptor));
    }

    public function test_euclidean_distance_increases_for_different_descriptors(): void
    {
        $service = app(FaceVerificationService::class);
        $left = array_fill(0, 128, 0.1);
        $right = array_fill(0, 128, 0.9);

        $distance = $service->euclideanDistance($left, $right);

        $this->assertGreaterThan(2.0, $distance);
    }

    public function test_normalize_descriptor_rejects_invalid_length(): void
    {
        $service = app(FaceVerificationService::class);

        $this->expectException(ValidationException::class);
        $service->normalizeDescriptor(array_fill(0, 64, 0.5));
    }

    public function test_compare_descriptors_matches_identical_vectors(): void
    {
        $service = app(FaceVerificationService::class);
        $descriptor = array_map(static fn (int $i) => round($i / 128, 6), range(0, 127));

        $result = $service->compareDescriptors($descriptor, $descriptor);

        $this->assertTrue($result->matched);
        $this->assertSame(0.0, $result->distance);
        $this->assertSame('ktp_upload', $result->source);
    }

    public function test_compare_calls_ensure_for_resident_before_checking_references(): void
    {
        $rt = RtProfile::create([
            'rt_number' => '001',
            'rw_number' => '001',
            'kelurahan' => 'Kelurahan Inauga',
            'kecamatan' => 'Distrik Wania',
            'kota' => 'Kabupaten Mimika',
            'provinsi' => 'Papua Tengah',
            'ketua_rt' => 'Ketua RT 001',
        ]);

        $household = Household::create([
            'rt_profile_id' => $rt->id,
            'family_card_number' => '3201010101010099',
            'address' => 'Jl. Uji No. 1',
            'status' => 'aktif',
            'pendataan_category' => 'warga_baru',
            'status_rumah_tinggal' => 'Kontrak',
            'suku' => 'Mee',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010001',
            'name' => 'Warga Uji',
            'phone' => '081234567890',
            'birth_place' => 'Timika',
            'birth_date' => '1990-01-01',
            'gender' => 'Laki-laki',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
        ]);

        $this->mock(ResidentFaceReferenceService::class, function ($mock) use ($resident) {
            $mock->shouldReceive('ensureForResident')
                ->once()
                ->with(\Mockery::on(fn ($arg) => $arg->id === $resident->id));
            $mock->shouldReceive('referencesForResident')
                ->andReturn(new Collection);
            $mock->shouldReceive('readinessForResident')
                ->andReturn(SuratFaceReadiness::missingDocuments());
        });

        $service = app(FaceVerificationService::class);

        try {
            $service->compare(array_fill(0, 128, 0.5), $resident);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString(
                'belum diunggah',
                $exception->errors()['face_descriptor'][0],
            );
        }
    }
}
