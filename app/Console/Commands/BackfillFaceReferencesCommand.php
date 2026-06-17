<?php

namespace App\Console\Commands;

use App\Models\Household;
use App\Models\PendataanDocument;
use App\Services\ResidentFaceReferenceService;
use App\Support\SuratFaceReadiness;
use Illuminate\Console\Command;

class BackfillFaceReferencesCommand extends Command
{
    protected $signature = 'face:backfill-references
                            {--household= : ID KK tertentu}
                            {--rt= : ID profil RT untuk membatasi audit}
                            {--audit : Tampilkan KK aktif yang belum siap verifikasi surat tanpa mengekstrak ulang}';

    protected $description = 'Ekstrak ulang referensi wajah dari dokumen KTP/KIA pendataan';

    public function handle(ResidentFaceReferenceService $service): int
    {
        if ($this->option('audit')) {
            return $this->runAudit($service);
        }

        $query = PendataanDocument::query()
            ->where(function ($builder) {
                $builder->where('document_type', 'ktp_kepala')
                    ->orWhere('document_type', 'like', 'ktp_a%')
                    ->orWhere('document_type', 'like', 'kia_a%');
            });

        if ($householdId = $this->option('household')) {
            $query->where('household_id', $householdId);
        }

        $processed = 0;
        $succeeded = 0;
        $failed = 0;

        $query->orderBy('id')->chunkById(50, function ($documents) use ($service, &$processed, &$succeeded, &$failed) {
            foreach ($documents as $document) {
                $result = $service->syncFromDocument($document);
                $processed++;

                if ($result['ok']) {
                    $succeeded++;
                    $this->line("OK   document #{$document->id} ({$document->document_type}) → {$result['message']}");
                } else {
                    $failed++;
                    $this->error("FAIL document #{$document->id} ({$document->document_type}) → {$result['message']}");
                }
            }
        });

        $this->info("Selesai memproses {$processed} dokumen ({$succeeded} berhasil, {$failed} gagal).");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function runAudit(ResidentFaceReferenceService $service): int
    {
        $rtProfileId = $this->option('rt') !== null ? (int) $this->option('rt') : null;
        $householdId = $this->option('household') !== null ? (int) $this->option('household') : null;

        if ($householdId !== null) {
            $household = Household::query()
                ->with(['pendataanDocuments', 'residents'])
                ->find($householdId);

            if (! $household) {
                $this->error("KK #{$householdId} tidak ditemukan.");

                return self::FAILURE;
            }

            $this->printAuditRow($household, $service->readinessForHousehold($household));
            $this->info('Audit selesai untuk 1 KK.');

            return self::SUCCESS;
        }

        $missingDocuments = 0;
        $extractionFailed = 0;
        $ready = 0;

        $query = Household::query()
            ->with(['pendataanDocuments', 'residents'])
            ->where('status', 'aktif')
            ->orderBy('family_card_number');

        if ($rtProfileId !== null) {
            $query->where('rt_profile_id', $rtProfileId);
        }

        foreach ($query->cursor() as $household) {
            $readiness = $service->readinessForHousehold($household);

            if ($readiness->status === SuratFaceReadiness::STATUS_READY) {
                $ready++;

                continue;
            }

            if ($readiness->status === SuratFaceReadiness::STATUS_MISSING_DOCUMENTS) {
                $missingDocuments++;
            } else {
                $extractionFailed++;
            }

            $this->printAuditRow($household, $readiness);
        }

        $this->newLine();
        $this->info("Audit selesai: {$ready} siap surat, {$missingDocuments} perlu KTP/KIA, {$extractionFailed} perlu unggah ulang.");

        return ($missingDocuments + $extractionFailed) > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function printAuditRow(Household $household, \App\Support\SuratFaceReadiness $readiness): void
    {
        $kk = $household->family_card_number ?: "(KK #{$household->id})";
        $this->line("{$readiness->adminLabel}  {$kk}  [{$readiness->status}]");
    }
}
