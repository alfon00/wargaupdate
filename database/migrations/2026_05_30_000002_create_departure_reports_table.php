<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departure_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->foreignId('rt_profile_id')->constrained('rt_profiles')->cascadeOnDelete();
            $table->foreignId('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->foreignId('household_id')->nullable()->constrained('households')->nullOnDelete();
            $table->string('scope', 20)->default('individual');
            $table->string('departure_reason', 32);
            $table->date('event_date');
            $table->string('reporter_name');
            $table->string('reporter_phone', 20);
            $table->string('reporter_relationship', 80)->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('menunggu');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('response_note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['rt_profile_id', 'status']);
            $table->index(['resident_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departure_reports');
    }
};
