<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class NormalizeMisassignedUsers extends Command
{
    protected $signature = 'lw:normalize-user-roles {--dry-run : Tampilkan perubahan tanpa menyimpan}';

    protected $description = 'Perbaiki akun admin yang salah di-assign sebagai Ketua/Sekretaris RT di profil publik';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $candidates = User::query()
            ->whereIn('role', [UserRole::KetuaRt, UserRole::SekretarisRt])
            ->whereNotNull('rt_profile_id')
            ->where(function ($q) {
                $q->where('name', 'like', 'Admin%')
                    ->orWhere('email', 'like', 'admin%');
            })
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('Tidak ada akun yang perlu dinormalisasi.');

            return self::SUCCESS;
        }

        foreach ($candidates as $user) {
            $this->line(sprintf(
                '- %s (%s) → super_admin, rt_profile_id = null',
                $user->email,
                $user->name
            ));

            if (! $dryRun) {
                $user->update([
                    'role' => UserRole::SuperAdmin,
                    'rt_profile_id' => null,
                ]);
            }
        }

        $this->info($dryRun
            ? 'Dry-run selesai. Jalankan tanpa --dry-run untuk menerapkan.'
            : 'Normalisasi selesai.');

        return self::SUCCESS;
    }
}
