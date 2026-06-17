<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_identity_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('selfie_path');
            $table->decimal('match_distance', 8, 4);
            $table->string('match_source', 10);
            $table->foreignId('reference_document_id')->nullable()->constrained('pendataan_documents')->nullOnDelete();
            $table->timestamp('verified_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_identity_verifications');
    }
};
