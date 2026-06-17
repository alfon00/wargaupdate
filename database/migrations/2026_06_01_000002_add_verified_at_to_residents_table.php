<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            if (! Schema::hasColumn('residents', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('domicile_status');
            }
            if (! Schema::hasColumn('residents', 'verification_notes')) {
                $table->text('verification_notes')->nullable()->after('verified_at');
            }
            if (! Schema::hasColumn('residents', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('verification_notes')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            if (Schema::hasColumn('residents', 'verified_by')) {
                $table->dropConstrainedForeignId('verified_by');
            }
            if (Schema::hasColumn('residents', 'verification_notes')) {
                $table->dropColumn('verification_notes');
            }
            if (Schema::hasColumn('residents', 'verified_at')) {
                $table->dropColumn('verified_at');
            }
        });
    }
};
