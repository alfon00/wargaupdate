<?php

namespace App\Console\Commands;

use App\Models\Household;
use App\Models\RtProfile;
use App\Models\RtPublication;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileRtProfiles extends Command
{
    protected $signature = 'lw:reconcile-rt-profiles {--apply : Terapkan perubahan ke database}';

    protected $description = 'Satukan duplikat rt_profiles Inauga ke profil kanonik per nomor RT';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $dryRun = ! $apply;

        if ($dryRun) {
            $this->warn('Mode dry-run (default). Jalankan dengan --apply untuk menyimpan perubahan.');
        }

        $groups = RtProfile::inauga()
            ->orderBy('rt_number')
            ->orderBy('id')
            ->get()
            ->groupBy('rt_number');

        $totalMoves = 0;
        $totalDeletes = 0;

        foreach ($groups as $rtNumber => $profiles) {
            if ($profiles->count() < 2) {
                continue;
            }

            $canonicalId = RtProfile::canonicalProfileIdForRtNumber((string) $rtNumber);
            if (! $canonicalId) {
                continue;
            }

            $duplicates = $profiles->where('id', '!=', $canonicalId);

            $this->line('');
            $this->info("RT {$rtNumber}: kanonik = id {$canonicalId}, duplikat = ".$duplicates->pluck('id')->join(', '));

            foreach ($duplicates as $duplicate) {
                $dupId = (int) $duplicate->id;

                $households = Household::where('rt_profile_id', $dupId)->count();
                $users = User::where('rt_profile_id', $dupId)->count();
                $publications = RtPublication::where('rt_profile_id', $dupId)->count();

                if ($households + $users + $publications === 0) {
                    $this->line("  - id {$dupId}: kosong → hapus");
                    if ($apply) {
                        $duplicate->delete();
                    }
                    $totalDeletes++;

                    continue;
                }

                $this->line("  - id {$dupId}: pindahkan {$households} KK, {$users} user, {$publications} publikasi → id {$canonicalId}");

                if ($apply) {
                    DB::transaction(function () use ($dupId, $canonicalId) {
                        Household::where('rt_profile_id', $dupId)->update(['rt_profile_id' => $canonicalId]);
                        User::where('rt_profile_id', $dupId)->update(['rt_profile_id' => $canonicalId]);
                        RtPublication::where('rt_profile_id', $dupId)->update(['rt_profile_id' => $canonicalId]);
                    });

                    $remaining = Household::where('rt_profile_id', $dupId)->count()
                        + User::where('rt_profile_id', $dupId)->count()
                        + RtPublication::where('rt_profile_id', $dupId)->count();

                    if ($remaining === 0) {
                        $duplicate->delete();
                        $totalDeletes++;
                    }
                }

                $totalMoves++;
            }
        }

        if ($groups->filter(fn ($g) => $g->count() > 1)->isEmpty()) {
            $this->info('Tidak ada duplikat rt_profiles Inauga yang perlu direkonsiliasi.');
        } elseif ($dryRun) {
            $this->line('');
            $this->comment("Ringkasan dry-run: {$totalMoves} profil duplikat akan dipindahkan, {$totalDeletes} baris kosong akan dihapus.");
            $this->comment('Jalankan: php artisan lw:reconcile-rt-profiles --apply');
        } else {
            $this->info("Selesai: {$totalMoves} profil dipindahkan, {$totalDeletes} duplikat dihapus.");
        }

        return self::SUCCESS;
    }
}
