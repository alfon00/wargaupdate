<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permanent_deletion_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('rt_profile_id')->constrained('rt_profiles')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('target_type', 20);
            $table->foreignId('resident_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->foreignId('household_id')->nullable()->constrained('households')->nullOnDelete();
            $table->string('target_name');
            $table->string('target_nik', 16)->nullable();
            $table->string('family_card_number', 16)->nullable();
            $table->string('signature_path');
            $table->string('status', 20)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['target_type', 'resident_id']);
            $table->index(['target_type', 'household_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permanent_deletion_requests');
    }
};
