<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('rt_profiles', 'stamp_path')) {
            Schema::table('rt_profiles', function (Blueprint $table) {
                $table->dropColumn('stamp_path');
            });
        }

        if (Schema::hasColumn('generated_letters', 'stamp_path')) {
            Schema::table('generated_letters', function (Blueprint $table) {
                $table->dropColumn('stamp_path');
            });
        }

        try {
            Storage::disk('local')->deleteDirectory('stamps');
        } catch (\Throwable) {
            // Best-effort cleanup; do not fail migration.
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('rt_profiles', 'stamp_path')) {
            Schema::table('rt_profiles', function (Blueprint $table) {
                $table->string('stamp_path')->nullable()->after('logo_path');
            });
        }

        if (! Schema::hasColumn('generated_letters', 'stamp_path')) {
            Schema::table('generated_letters', function (Blueprint $table) {
                $table->string('stamp_path')->nullable()->after('signature_path');
            });
        }
    }
};
