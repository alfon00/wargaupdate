<?php

namespace App\Services;

use App\Models\Resident;
use App\Models\SuratIdentityVerification;
use App\Support\FaceMatchResult;
use App\Support\PrivateStorageDirectory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FaceVerificationService
{
    public function __construct(
        private readonly ResidentFaceReferenceService $references,
    ) {}

    /**
     * @param  array<int, float|int|string>  $selfieDescriptor
     */
    public function compare(array $selfieDescriptor, Resident $resident): FaceMatchResult
    {
        $selfie = $this->normalizeDescriptor($selfieDescriptor);
        $this->references->ensureForResident($resident);
        $refs = $this->references->referencesForResident($resident);

        if ($refs->isEmpty()) {
            throw ValidationException::withMessages([
                'face_descriptor' => $this->missingReferenceMessage($resident),
            ]);
        }

        $bestDistance = PHP_FLOAT_MAX;
        $bestSource = 'ktp';
        $bestDocumentId = null;
        $bestFaceIndex = null;

        foreach ($refs as $reference) {
            $distance = $this->euclideanDistance($selfie, $reference->descriptor ?? []);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestSource = $reference->source;
                $bestDocumentId = $reference->pendataan_document_id;
                $bestFaceIndex = $reference->face_index;
            }
        }

        $threshold = (float) config('kelurahan.face_match_threshold', 0.6);

        return new FaceMatchResult(
            matched: $bestDistance <= $threshold,
            distance: $bestDistance,
            source: $bestSource,
            referenceDocumentId: $bestDocumentId,
            faceIndex: $bestFaceIndex,
        );
    }

    /**
     * @param  array<int, float|int|string>  $selfieDescriptor
     * @param  array<int, float|int|string>  $referenceDescriptor
     */
    public function compareDescriptors(array $selfieDescriptor, array $referenceDescriptor): FaceMatchResult
    {
        $selfie = $this->normalizeDescriptor($selfieDescriptor);
        $reference = $this->normalizeDescriptor($referenceDescriptor);
        $distance = $this->euclideanDistance($selfie, $reference);
        $threshold = (float) config('kelurahan.face_match_threshold', 0.6);

        return new FaceMatchResult(
            matched: $distance <= $threshold,
            distance: $distance,
            source: 'ktp_upload',
        );
    }

    /**
     * @param  array<int, float|int|string>  $selfieDescriptor
     */
    public function verifyAndStore(
        Request $request,
        Resident $resident,
        array $selfieDescriptor,
        string $selfieDataUri,
    ): SuratIdentityVerification {
        $match = $this->compare($selfieDescriptor, $resident);

        if (! $match->matched) {
            throw ValidationException::withMessages([
                'face_descriptor' => 'Verifikasi wajah gagal. Pastikan pencahayaan cukup dan wajah sesuai foto KTP/KIA terdaftar.',
            ]);
        }

        $selfiePath = $this->storeSelfie($resident, $selfieDataUri);

        return SuratIdentityVerification::create([
            'resident_id' => $resident->id,
            'selfie_path' => $selfiePath,
            'match_distance' => $match->distance,
            'match_source' => $match->source,
            'reference_document_id' => $match->referenceDocumentId,
            'verified_at' => now(),
            'ip_address' => $request->ip(),
        ]);
    }

    protected function storeSelfie(Resident $resident, string $dataUri): string
    {
        if (! preg_match('#^data:image/(jpeg|jpg|png);base64,#i', $dataUri, $matches)) {
            throw ValidationException::withMessages([
                'selfie_data' => 'Format foto selfie tidak valid.',
            ]);
        }

        $raw = base64_decode(substr($dataUri, strlen($matches[0])), true);
        if ($raw === false || strlen($raw) < 1000) {
            throw ValidationException::withMessages([
                'selfie_data' => 'Foto selfie tidak valid atau terlalu kecil.',
            ]);
        }

        $ext = strtolower($matches[1]) === 'png' ? 'png' : 'jpg';
        $directory = "surat-verifications/{$resident->id}";
        PrivateStorageDirectory::ensureWritable($directory);

        $path = "{$directory}/".Str::ulid().".{$ext}";
        PrivateStorageDirectory::write($path, $raw);

        return $path;
    }

    /**
     * @param  array<int, float|int|string>  $descriptor
     * @return array<int, float>
     */
    public function normalizeDescriptor(array $descriptor): array
    {
        if (count($descriptor) !== 128) {
            throw ValidationException::withMessages([
                'face_descriptor' => 'Data wajah tidak valid. Ambil ulang foto selfie.',
            ]);
        }

        return array_map('floatval', $descriptor);
    }

    /**
     * @param  array<int, float>  $a
     * @param  array<int, float>  $b
     */
    public function euclideanDistance(array $a, array $b): float
    {
        if (count($a) !== 128 || count($b) !== 128) {
            return PHP_FLOAT_MAX;
        }

        $sum = 0.0;
        for ($i = 0; $i < 128; $i++) {
            $diff = $a[$i] - $b[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    public function linkToApplication(int $verificationId, int $applicationId): void
    {
        SuratIdentityVerification::query()
            ->whereKey($verificationId)
            ->whereNull('application_id')
            ->update(['application_id' => $applicationId]);
    }

    private function missingReferenceMessage(Resident $resident): string
    {
        return $this->references->readinessForResident($resident)->message;
    }
}
