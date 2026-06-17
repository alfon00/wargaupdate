<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            if (! Schema::hasColumn('households', 'pendataan_category')) {
                $table->string('pendataan_category', 30)->default('warga_baru')->after('registration_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            if (Schema::hasColumn('households', 'pendataan_category')) {
                $table->dropColumn('pendataan_category');
            }
        });
    }
};
