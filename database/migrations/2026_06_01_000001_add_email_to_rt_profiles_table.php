<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rt_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('rt_profiles', 'email')) {
                $table->string('email')->nullable()->after('whatsapp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rt_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('rt_profiles', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
