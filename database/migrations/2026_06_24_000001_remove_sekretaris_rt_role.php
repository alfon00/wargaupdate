<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'sekretaris_rt')
            ->delete();

        Schema::table('rt_profiles', function (Blueprint $table) {
            $table->dropColumn('sekretaris_rt');
        });
    }

    public function down(): void
    {
        Schema::table('rt_profiles', function (Blueprint $table) {
            $table->string('sekretaris_rt')->nullable()->after('ketua_rw');
        });
    }
};
