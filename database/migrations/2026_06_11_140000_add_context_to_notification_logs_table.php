<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->foreignId('citizen_report_id')->nullable()->after('resident_id')
                ->constrained('citizen_reports')->nullOnDelete();
            $table->foreignId('rt_publication_id')->nullable()->after('citizen_report_id')
                ->constrained('rt_publications')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('citizen_report_id');
            $table->dropConstrainedForeignId('rt_publication_id');
        });
    }
};
