<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resident_face_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pendataan_document_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 10);
            $table->unsignedSmallInteger('face_index')->default(0);
            $table->json('descriptor');
            $table->timestamp('extracted_at');
            $table->timestamps();

            $table->index(['resident_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_face_references');
    }
};
