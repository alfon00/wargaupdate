<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizen_reports', function (Blueprint $table) {
            $table->string('incident_location', 200)->nullable()->after('message');
            $table->string('incident_type', 64)->nullable()->after('incident_location');
            $table->string('photo_path')->nullable()->after('incident_type');
        });
    }

    public function down(): void
    {
        Schema::table('citizen_reports', function (Blueprint $table) {
            $table->dropColumn(['incident_location', 'incident_type', 'photo_path']);
        });
    }
};
