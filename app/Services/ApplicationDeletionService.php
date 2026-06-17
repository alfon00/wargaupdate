<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicationDeletionService
{
    public function delete(Application $application): void
    {
        DB::transaction(function () use ($application) {
            $application->loadMissing([
                'documents',
                'generatedLetter',
                'suratIdentityVerification',
            ]);

            foreach ($application->documents as $document) {
                $this->deleteStorageFile($document->file_path);
            }

            if ($letter = $application->generatedLetter) {
                $this->deleteStorageFile($letter->file_path);
                $this->deleteStorageFile($letter->signature_path);
            }

            if ($verification = $application->suratIdentityVerification) {
                $this->deleteStorageFile($verification->selfie_path);
                $verification->delete();
            }

            $application->delete();
        });
    }

    private function deleteStorageFile(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }
}
