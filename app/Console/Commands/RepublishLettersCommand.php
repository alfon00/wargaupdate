<?php

namespace App\Console\Commands;

use App\Models\GeneratedLetter;
use App\Services\LetterGeneratorService;
use App\Support\LetterTemplateSeeder;
use Illuminate\Console\Command;

class RepublishLettersCommand extends Command
{
    protected $signature = 'lw:republish-letters
                            {--refresh-templates : Perbarui template HTML sebelum menerbitkan ulang}
                            {--application= : Nomor permohonan spesifik, mis. RT008-2026060001}';

    protected $description = 'Terbitkan ulang PDF surat yang sudah ada dengan template aktif terbaru';

    public function handle(LetterGeneratorService $letters): int
    {
        if ($this->option('refresh-templates')) {
            $count = LetterTemplateSeeder::refreshAll();
            $this->info("{$count} template surat diperbarui.");
        }

        $query = GeneratedLetter::query()
            ->with(['application.serviceType', 'application.generatedLetter'])
            ->whereNotNull('file_path');

        if ($applicationNumber = $this->option('application')) {
            $query->whereHas('application', fn ($q) => $q->where('application_number', $applicationNumber));
        }

        $lettersToRepublish = $query->get();

        if ($lettersToRepublish->isEmpty()) {
            $this->warn('Tidak ada surat PDF yang perlu diterbitkan ulang.');

            return self::SUCCESS;
        }

        $success = 0;
        $failed = 0;

        foreach ($lettersToRepublish as $generatedLetter) {
            $application = $generatedLetter->application;
            if (! $application) {
                $this->error("Surat id {$generatedLetter->id}: permohonan tidak ditemukan.");
                $failed++;

                continue;
            }

            try {
                $oldPath = $generatedLetter->file_path;
                $updated = $letters->republish($application);
                if ($oldPath && $oldPath !== $updated->file_path) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($oldPath);
                }
                $this->info("{$application->application_number}: PDF diterbitkan ulang → {$updated->file_path}");
                $success++;
            } catch (\Throwable $e) {
                $this->error("{$application->application_number}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Selesai: {$success} berhasil, {$failed} gagal.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
