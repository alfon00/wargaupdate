<?php

namespace App\Services;

use App\Models\CitizenReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CitizenReportDeletionService
{
    public function delete(CitizenReport $report): void
    {
        DB::transaction(function () use ($report) {
            $this->deleteStorageFile($report->photo_path);
            $report->delete();
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
