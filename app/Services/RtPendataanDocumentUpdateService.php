<?php

namespace App\Services;

use App\Models\Household;
use App\Models\PendataanDocument;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class RtPendataanDocumentUpdateService
{
    /** @var list<string> */
    private const HOUSEHOLD_DOCUMENT_TYPES = ['kk', 'lampiran'];

    public function __construct(
        private readonly PendataanDocumentStorage $documentStorage,
        private readonly GuestResidentService $guestResidentService,
    ) {}

    public function updateFromRequest(Household $household, Request $request, Resident $resident): ?string
    {
        if (! $resident->is_head_of_family && $this->hasHouseholdDocumentChanges($request)) {
            throw ValidationException::withMessages([
                'document_kk' => 'Scan KK dan lampiran keluarga hanya dapat diperbarui saat mengedit kepala keluarga.',
            ]);
        }

        $validated = $request->validate(
            $this->rules($resident),
            $this->guestResidentService->pendataanDocumentMessages(),
        );

        $allowedTypes = $this->guestResidentService->identityDocumentTypesForResident($resident);

        foreach ($validated['remove_identity_document'] ?? [] as $documentId) {
            $document = PendataanDocument::query()
                ->where('id', $documentId)
                ->where('household_id', $household->id)
                ->first();

            if (! $document) {
                throw ValidationException::withMessages([
                    'remove_identity_document' => 'Berkas tidak ditemukan atau bukan milik anggota ini.',
                ]);
            }

            if (! in_array($document->document_type, $allowedTypes, true)) {
                throw ValidationException::withMessages([
                    'remove_identity_document' => 'Hanya berkas identitas anggota ini yang dapat dihapus.',
                ]);
            }

            $this->documentStorage->delete($document);
        }

        if ($request->file('document_identity') instanceof UploadedFile) {
            $documentType = $this->guestResidentService->primaryIdentityDocumentTypeForResident($resident);
            $this->documentStorage->replace($household, $request->file('document_identity'), $documentType);
        }

        if ($resident->is_head_of_family) {
            foreach ($validated['remove_household_document'] ?? [] as $documentId) {
                $document = PendataanDocument::query()
                    ->where('id', $documentId)
                    ->where('household_id', $household->id)
                    ->first();

                if (! $document) {
                    throw ValidationException::withMessages([
                        'remove_household_document' => 'Berkas tidak ditemukan atau bukan milik kartu keluarga ini.',
                    ]);
                }

                if (! in_array($document->document_type, self::HOUSEHOLD_DOCUMENT_TYPES, true)) {
                    throw ValidationException::withMessages([
                        'remove_household_document' => 'Hanya scan KK dan lampiran tambahan yang dapat dihapus dari sini.',
                    ]);
                }

                $this->documentStorage->delete($document);
            }

            if ($request->file('document_kk') instanceof UploadedFile) {
                $this->documentStorage->replace($household, $request->file('document_kk'), 'kk');
            }

            $lampiranFiles = array_filter(
                $request->file('documents') ?? [],
                fn ($file) => $file instanceof UploadedFile,
            );

            if ($lampiranFiles !== []) {
                $this->documentStorage->appendLampiran($household, $lampiranFiles);
            }
        }

        return $this->documentStorage->consumeFaceSyncWarning();
    }

    private function hasHouseholdDocumentChanges(Request $request): bool
    {
        if ($request->hasFile('document_kk') || $request->hasFile('documents')) {
            return true;
        }

        $removeIds = $request->input('remove_household_document');

        return is_array($removeIds) && $removeIds !== [];
    }

    /** @return array<string, mixed> */
    private function rules(Resident $resident): array
    {
        $rules = [
            'document_identity' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'remove_identity_document' => ['nullable', 'array'],
            'remove_identity_document.*' => ['integer'],
        ];

        if ($resident->is_head_of_family) {
            $rules['document_kk'] = ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'];
            $rules['documents'] = ['nullable', 'array'];
            $rules['documents.*'] = ['file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'];
            $rules['remove_household_document'] = ['nullable', 'array'];
            $rules['remove_household_document.*'] = ['integer'];
        }

        return $rules;
    }
}
