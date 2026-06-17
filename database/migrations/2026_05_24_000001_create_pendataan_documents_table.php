<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            if (! Schema::hasColumn('households', 'registration_type')) {
                $table->string('registration_type', 20)->nullable()->after('status');
            }
        });

        Schema::create('pendataan_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->string('document_type', 30);
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendataan_documents');

        Schema::table('households', function (Blueprint $table) {
            if (Schema::hasColumn('households', 'registration_type')) {
                $table->dropColumn('registration_type');
            }
        });
    }
};
