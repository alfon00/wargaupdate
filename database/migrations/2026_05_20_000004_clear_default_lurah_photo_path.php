<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $official = DB::table('kelurahan_officials')->where('role', 'lurah')->first();
        if (! $official) {
            return;
        }

        $path = $official->photo_path;
        if (! $path || str_starts_with($path, 'images/')) {
            DB::table('kelurahan_officials')
                ->where('role', 'lurah')
                ->update(['photo_path' => null, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Tidak mengembalikan foto placeholder config.
    }
};
