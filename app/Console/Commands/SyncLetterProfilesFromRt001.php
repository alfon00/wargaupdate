<?php

namespace App\Console\Commands;

use App\Models\RtProfile;
use Illuminate\Console\Command;

class SyncLetterProfilesFromRt001 extends Command
{
    protected $signature = 'lw:sync-letter-profiles-from-rt001 {--apply : Terapkan perubahan ke database}';

    protected $description = 'Samakan field kop surat (wilayah, alamat kantor) semua RT Inauga mengikuti profil kanonik RT001';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->warn('Mode dry-run. Gunakan --apply untuk menyimpan perubahan.');
        }

        $baselineId = RtProfile::canonicalProfileIdForRtNumber('001');
        $baseline = $baselineId ? RtProfile::find($baselineId) : null;

        if (! $baseline) {
            $this->error('Profil kanonik RT001 tidak ditemukan di wilayah Inauga.');

            return self::FAILURE;
        }

        foreach (['kelurahan', 'kecamatan', 'kota', 'provinsi'] as $field) {
            if (! filled($baseline->{$field})) {
                $this->error("RT001 acuan: kolom \"{$field}\" masih kosong. Lengkapi profil RT001 terlebih dahulu.");

                return self::FAILURE;
            }
        }

        $this->info("Acuan: RT001 (id {$baseline->id}) — {$baseline->kelurahan}");

        $rtNumbers = RtProfile::inauga()
            ->pluck('rt_number')
            ->unique()
            ->filter();

        $updated = 0;
        $skipped = 0;

        foreach ($rtNumbers as $rtNumber) {
            if (RtProfile::normalizeRtNumber((string) $rtNumber) === '001') {
                continue;
            }

            $targetId = RtProfile::canonicalProfileIdForRtNumber((string) $rtNumber);
            if (! $targetId) {
                $this->line("  RT {$rtNumber}: tidak ada profil kanonik — lewati");
                $skipped++;

                continue;
            }

            $target = RtProfile::find($targetId);
            if (! $target) {
                $skipped++;

                continue;
            }

            $payload = [
                'kelurahan' => $baseline->kelurahan,
                'kecamatan' => $baseline->kecamatan,
                'kota' => $baseline->kota,
                'provinsi' => $baseline->provinsi,
            ];

            if (! filled($target->rw_number) && filled($baseline->rw_number)) {
                $payload['rw_number'] = $baseline->rw_number;
            }

            $newAddress = $baseline->letterKopAddressForRtNumber((string) $target->rt_number, $baseline->alamat_kantor);
            if (filled($newAddress)) {
                $payload['alamat_kantor'] = $newAddress;
            }

            $changes = [];
            foreach ($payload as $key => $value) {
                if ((string) ($target->{$key} ?? '') !== (string) $value) {
                    $changes[$key] = $value;
                }
            }

            if ($changes === []) {
                $this->line("  RT {$rtNumber} (id {$target->id}): sudah selaras");
                $skipped++;

                continue;
            }

            $this->line('  RT '.$rtNumber.' (id '.$target->id.'): '.implode(', ', array_keys($changes)));

            if ($apply) {
                $target->update($changes);
            }

            $updated++;
        }

        if ($apply) {
            $this->info("Selesai: {$updated} profil diperbarui, {$skipped} dilewati/tidak berubah.");
        } else {
            $this->comment("Dry-run: {$updated} profil akan diperbarui, {$skipped} dilewati/tidak berubah.");
            $this->comment('Jalankan: php artisan lw:sync-letter-profiles-from-rt001 --apply');
        }

        return self::SUCCESS;
    }
}
