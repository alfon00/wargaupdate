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
    public function __construct(
        private readonly PendataanDocumentStorage $documentStorage,
        private readonly GuestResidentService $guestResidentService,
    ) {}

    public function updateFromRequest(Household $household, Request $request, Resident $resident): ?string
    {
        $validated = $request->validate(
            $this->rules(),
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

        return $this->documentStorage->consumeFaceSyncWarning();
    }

    /** @return array<string, mixed> */
    private function rules(): array
    {
        return [
            'document_identity' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'remove_identity_document' => ['nullable', 'array'],
            'remove_identity_document.*' => ['integer'],
        ];
    }
}
