<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('rt_profiles', 'stamp_path')) {
            Schema::table('rt_profiles', function (Blueprint $table) {
                $table->string('stamp_path')->nullable()->after('logo_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('rt_profiles', 'stamp_path')) {
            Schema::table('rt_profiles', function (Blueprint $table) {
                $table->dropColumn('stamp_path');
            });
        }
    }
};
