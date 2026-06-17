<?php

namespace App\Services;

use App\Models\Household;
use App\Models\PendataanDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use League\Flysystem\UnableToCreateDirectory;

class PendataanDocumentStorage
{
    private ?string $faceSyncWarning = null;

    public function __construct(
        private readonly ResidentFaceReferenceService $faceReferences,
    ) {}

    public function consumeFaceSyncWarning(): ?string
    {
        $warning = $this->faceSyncWarning;
        $this->faceSyncWarning = null;

        return $warning;
    }

    public function directoryFor(Household $household): string
    {
        $rtId = $household->rt_profile_id;

        return "pendataan/rt-{$rtId}/household-{$household->id}";
    }

    public function store(Household $household, UploadedFile $file, string $documentType): PendataanDocument
    {
        $directory = $this->directoryFor($household);
        $field = $this->fieldForDocumentType($documentType);
        $this->ensureDirectory($directory, $field);

        $basename = Str::ulid().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $storedName = $extension !== '' ? "{$basename}.{$extension}" : $basename;

        try {
            $path = $file->storeAs($directory, $storedName, 'local');
        } catch (UnableToCreateDirectory) {
            throw $this->storageUnavailableException($field);
        }

        if ($path === false) {
            throw $this->storageUnavailableException($field);
        }

        $document = PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => $documentType,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
        ]);

        if ($this->faceReferences->supportsFaceExtraction($documentType)) {
            $result = $this->faceReferences->syncFromDocument($document);

            if (! $result['ok']) {
                $this->faceSyncWarning = 'Wajah pada berkas tidak terdeteksi untuk referensi sistem. '
                    .$result['message']
                    .' Unggah foto JPG/PNG KTP/KIA yang jelas jika perlu.';
            }
        }

        return $document;
    }

    public function delete(PendataanDocument $document): void
    {
        if ($document->file_path && Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();
    }

    public function replace(Household $household, UploadedFile $file, string $documentType): PendataanDocument
    {
        $existing = PendataanDocument::query()
            ->where('household_id', $household->id)
            ->where('document_type', $documentType)
            ->get();

        foreach ($existing as $document) {
            $this->delete($document);
        }

        return $this->store($household, $file, $documentType);
    }

    public function storeFromContents(
        Household $household,
        string $contents,
        string $documentType,
        string $originalName,
        string $mimeType,
    ): PendataanDocument {
        $directory = $this->directoryFor($household);
        $field = $this->fieldForDocumentType($documentType);
        $this->ensureDirectory($directory, $field);

        $basename = Str::ulid().'_'.Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $storedName = $extension !== '' ? "{$basename}.{$extension}" : $basename;
        $path = "{$directory}/{$storedName}";

        if (! Storage::disk('local')->put($path, $contents)) {
            throw $this->storageUnavailableException($field);
        }

        return PendataanDocument::create([
            'household_id' => $household->id,
            'document_type' => $documentType,
            'file_path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
        ]);
    }

    /** @param  iterable<int, UploadedFile>  $files */
    public function appendLampiran(Household $household, iterable $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->store($household, $file, 'lampiran');
            }
        }
    }

    private function ensureDirectory(string $relativePath, string $field): void
    {
        $disk = Storage::disk('local');

        if (! $disk->makeDirectory($relativePath)) {
            $absolute = storage_path('app/private/'.$relativePath);

            try {
                File::makeDirectory($absolute, 0755, true);
            } catch (\Throwable) {
                // Fall through to writability check below.
            }
        }

        $absolute = $disk->path($relativePath);

        if (! is_dir($absolute) || ! is_writable($absolute)) {
            throw $this->storageUnavailableException($field);
        }
    }

    private function fieldForDocumentType(string $documentType): string
    {
        return match ($documentType) {
            'kk' => 'document_kk',
            'ktp_kepala' => 'document_ktp',
            'selfie_kepala' => 'head_selfie_data',
            default => str_starts_with($documentType, 'ktp_a') || str_starts_with($documentType, 'kia_a')
                ? 'document_identity'
                : 'documents',
        };
    }

    private function storageUnavailableException(string $field): ValidationException
    {
        return ValidationException::withMessages([
            $field => 'Berkas gagal disimpan. Folder penyimpanan server belum siap — hubungi admin RT/kelurahan.',
        ]);
    }
}
