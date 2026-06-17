<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->timestamp('departed_at')->nullable()->after('verified_by');
            $table->string('departure_reason', 30)->nullable()->after('departed_at');
            $table->text('departure_notes')->nullable()->after('departure_reason');
            $table->foreignId('departed_by')->nullable()->after('departure_notes')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('departed_by');
            $table->dropColumn(['departed_at', 'departure_reason', 'departure_notes']);
        });
    }
};
