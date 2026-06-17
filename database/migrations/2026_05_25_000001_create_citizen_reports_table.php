<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citizen_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->foreignId('rt_profile_id')->constrained('rt_profiles')->cascadeOnDelete();
            $table->string('category', 32);
            $table->string('reporter_name');
            $table->string('phone', 20);
            $table->string('nik', 16)->nullable();
            $table->string('email')->nullable();
            $table->string('application_number')->nullable();
            $table->string('subject', 120);
            $table->text('message');
            $table->string('status')->default('baru');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('response_note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['rt_profile_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citizen_reports');
    }
};
