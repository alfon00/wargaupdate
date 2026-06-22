<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'super_admin')
            ->update(['role' => 'kelurahan']);

        DB::table('users')
            ->where('role', 'warga')
            ->delete();
    }

    public function down(): void
    {
        // Tidak dapat mengembalikan akun warga yang dihapus.
    }
};
