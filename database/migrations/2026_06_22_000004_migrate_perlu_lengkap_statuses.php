<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('applications')
            ->where('status', 'perlu_lengkap')
            ->update(['status' => 'diajukan']);

        DB::table('residents')
            ->where('domicile_status', 'perlu_lengkap')
            ->update(['domicile_status' => 'menunggu_verifikasi']);

        DB::table('households')
            ->where('status', 'perlu_lengkap')
            ->update(['status' => 'menunggu_verifikasi']);
    }

    public function down(): void
    {
        // Irreversible: cannot know which records were originally perlu_lengkap.
    }
};
