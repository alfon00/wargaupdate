<?php

namespace App\Services;

use App\Models\Household;
use App\Models\PendataanDocument;
use App\Models\Resident;
use App\Models\ResidentFaceReference;
use App\Support\SuratFaceReadiness;
use Illuminate\Support\Facades\Log;

class ResidentFaceReferenceService
{
    public function __construct(
        private readonly FaceDescriptorExtractor $extractor,
    ) {}

    public function supportsFaceExtraction(string $documentType): bool
    {
        if ($documentType === 'ktp_kepala') {
            return true;
        }

        return str_starts_with($documentType, 'ktp_a')
            || str_starts_with($documentType, 'kia_a');
    }

    /**
     * @return array{ok: bool, faces: int, message: string}
     */
    public function syncFromDocument(PendataanDocument $document): array
    {
        $document->loadMissing('household.residents');
        $household = $document->household;
        if (! $household) {
            return ['ok' => false, 'faces' => 0, 'message' => 'data KK tidak ditemukan'];
        }

        if (! $this->supportsFaceExtraction($document->document_type)) {
            return ['ok' => false, 'faces' => 0, 'message' => 'tipe dokumen tidak didukung'];
        }

        $resident = $this->residentForDocument($household, $document);
        if (! $resident) {
            return ['ok' => false, 'faces' => 0, 'message' => 'warga pemilik berkas tidak ditemukan'];
        }

        $source = str_starts_with($document->document_type, 'kia_') ? 'kia' : 'ktp';

        if (! $document->fileExists()) {
            $this->recordExtractionFailure($document, 'file dokumen tidak ada di storage');

            return ['ok' => false, 'faces' => 0, 'message' => 'file dokumen tidak ada di storage'];
        }

        $faces = $this->extractor->extractFromStoragePath($document->file_path);
        if ($faces === []) {
            $reason = $this->extractor->getLastError() ?? 'tidak ada wajah terdeteksi';

            Log::info('ResidentFaceReferenceService: no faces extracted', [
                'document_id' => $document->id,
                'type' => $document->document_type,
                'reason' => $reason,
            ]);

            $this->recordExtractionFailure($document, $reason);

            return ['ok' => false, 'faces' => 0, 'message' => $reason];
        }

        ResidentFaceReference::query()
            ->where('resident_id', $resident->id)
            ->where('pendataan_document_id', $document->id)
            ->delete();

        foreach ($faces as $face) {
            ResidentFaceReference::create([
                'resident_id' => $resident->id,
                'pendataan_document_id' => $document->id,
                'source' => $source,
                'face_index' => $face['face_index'],
                'descriptor' => $face['descriptor'],
                'extracted_at' => now(),
            ]);
        }

        $document->update([
            'face_extraction_error' => null,
            'face_extracted_at' => now(),
        ]);

        return [
            'ok' => true,
            'faces' => count($faces),
            'message' => count($faces).' wajah',
        ];
    }

    public function syncForHousehold(Household $household): void
    {
        $this->syncForHouseholdWithSummary($household);
    }

    /**
     * @return array{ok: bool, message: string, synced: int, failed: int, errors: list<string>}
     */
    public function syncForHouseholdWithSummary(Household $household): array
    {
        $household->loadMissing(['pendataanDocuments', 'residents']);

        $synced = 0;
        $failed = 0;
        $errors = [];

        foreach ($household->pendataanDocuments as $document) {
            if (! $this->supportsFaceExtraction($document->document_type)) {
                continue;
            }

            $result = $this->syncFromDocument($document);

            if ($result['ok']) {
                $synced++;

                continue;
            }

            $failed++;
            $errors[] = $document->typeLabel().': '.$result['message'];
        }

        $this->purgeLegacyKkReferences($household);

        if ($synced > 0 && $failed === 0) {
            return [
                'ok' => true,
                'message' => "Referensi wajah berhasil disinkronkan ({$synced} berkas).",
                'synced' => $synced,
                'failed' => $failed,
                'errors' => $errors,
            ];
        }

        if ($synced > 0) {
            return [
                'ok' => false,
                'message' => "Sebagian berhasil ({$synced}), sebagian gagal ({$failed}). ".implode(' ', $errors),
                'synced' => $synced,
                'failed' => $failed,
                'errors' => $errors,
            ];
        }

        if ($failed > 0) {
            return [
                'ok' => false,
                'message' => 'Sinkronisasi wajah gagal. '.implode(' ', $errors),
                'synced' => $synced,
                'failed' => $failed,
                'errors' => $errors,
            ];
        }

        return [
            'ok' => false,
            'message' => 'Tidak ada berkas KTP/KIA untuk disinkronkan.',
            'synced' => 0,
            'failed' => 0,
            'errors' => [],
        ];
    }

    public function ensureForResident(Resident $resident): void
    {
        $resident->loadMissing('household.pendataanDocuments');
        $household = $resident->household;

        if (! $household || ! $this->hasIdentityDocuments($resident)) {
            return;
        }

        if ($this->referencesForResident($resident)->isNotEmpty()) {
            return;
        }

        $this->syncForHousehold($household);
    }

    public function hasIdentityDocuments(Resident $resident): bool
    {
        $resident->loadMissing('household.pendataanDocuments');
        $household = $resident->household;

        if (! $household) {
            return false;
        }

        $types = $this->identityDocumentTypesForResident($resident);

        return $household->pendataanDocuments
            ->contains(fn (PendataanDocument $document) => in_array($document->document_type, $types, true));
    }

    public function referencesForResident(Resident $resident): \Illuminate\Database\Eloquent\Collection
    {
        return ResidentFaceReference::query()
            ->where('resident_id', $resident->id)
            ->orderBy('source')
            ->orderBy('face_index')
            ->get();
    }

    public function readinessForResident(Resident $resident, bool $attemptSync = false): SuratFaceReadiness
    {
        if (! $this->hasIdentityDocuments($resident)) {
            return SuratFaceReadiness::missingDocuments();
        }

        if ($attemptSync) {
            $this->ensureForResident($resident);
        }

        if ($this->referencesForResident($resident)->isEmpty()) {
            return SuratFaceReadiness::extractionFailed(
                $this->extractionDetailForResident($resident)
            );
        }

        return SuratFaceReadiness::ready();
    }

    public function readinessForHousehold(Household $household, bool $attemptSync = false): SuratFaceReadiness
    {
        $household->loadMissing(['pendataanDocuments', 'residents']);

        $head = $household->residents->firstWhere('is_head_of_family', true)
            ?? $household->residents->first();

        if (! $head) {
            return SuratFaceReadiness::missingDocuments();
        }

        return $this->readinessForResident($head, $attemptSync);
    }

    /**
     * @return list<array{household_id: int, family_card_number: string|null, status: string, admin_label: string}>
     */
    public function auditHouseholdsWithoutFaceReadiness(?int $rtProfileId = null): array
    {
        $query = Household::query()
            ->with(['pendataanDocuments', 'residents'])
            ->where('status', 'aktif')
            ->orderBy('family_card_number');

        if ($rtProfileId !== null) {
            $query->where('rt_profile_id', $rtProfileId);
        }

        $results = [];

        foreach ($query->cursor() as $household) {
            $readiness = $this->readinessForHousehold($household);

            if ($readiness->canVerify) {
                continue;
            }

            $results[] = [
                'household_id' => $household->id,
                'family_card_number' => $household->family_card_number,
                'status' => $readiness->status,
                'admin_label' => $readiness->adminLabel,
            ];
        }

        return $results;
    }

    public function residentForDocument(Household $household, PendataanDocument $document): ?Resident
    {
        $residents = $this->orderedResidents($household);

        if ($document->document_type === 'ktp_kepala') {
            return $residents->firstWhere('is_head_of_family', true)
                ?? $residents->first();
        }

        if (preg_match('/^(?:ktp|kia)_a(\d+)$/', $document->document_type, $matches)) {
            $index = (int) $matches[1];

            return $residents->get($index);
        }

        return null;
    }

    private function purgeLegacyKkReferences(Household $household): void
    {
        $residentIds = $household->residents->pluck('id');

        if ($residentIds->isEmpty()) {
            return;
        }

        ResidentFaceReference::query()
            ->whereIn('resident_id', $residentIds)
            ->where('source', 'kk')
            ->delete();
    }

    private function recordExtractionFailure(PendataanDocument $document, string $reason): void
    {
        $document->update([
            'face_extraction_error' => $reason,
            'face_extracted_at' => null,
        ]);
    }

    private function extractionDetailForResident(Resident $resident): ?string
    {
        $resident->loadMissing('household.pendataanDocuments');
        $household = $resident->household;

        if (! $household) {
            return null;
        }

        $types = $this->identityDocumentTypesForResident($resident);

        $errors = $household->pendataanDocuments
            ->filter(fn (PendataanDocument $document) => in_array($document->document_type, $types, true))
            ->pluck('face_extraction_error')
            ->filter(fn (?string $error) => is_string($error) && $error !== '')
            ->unique()
            ->values();

        if ($errors->isEmpty()) {
            return null;
        }

        return $errors->implode('; ');
    }

    /** @return \Illuminate\Support\Collection<int, Resident> */
    private function orderedResidents(Household $household): \Illuminate\Support\Collection
    {
        return $household->residents()
            ->orderByDesc('is_head_of_family')
            ->orderBy('id')
            ->get();
    }

    /** @return list<string> */
    private function identityDocumentTypesForResident(Resident $resident): array
    {
        $household = $resident->household;
        if (! $household) {
            return [];
        }

        $residents = $this->orderedResidents($household);
        $index = $residents->search(fn (Resident $member) => (int) $member->id === (int) $resident->id);

        if ($index === false) {
            return [];
        }

        $prefix = 'ktp';
        if ($resident->birth_date && $resident->birth_date->age < 17) {
            $prefix = 'kia';
        }

        $types = ["{$prefix}_a{$index}"];

        if ($index === 0 && $household->pendataanDocuments->contains('document_type', 'ktp_kepala')) {
            $types[] = 'ktp_kepala';
        }

        return array_values(array_unique($types));
    }
}
