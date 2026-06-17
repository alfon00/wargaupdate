<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendataan_documents', function (Blueprint $table) {
            $table->text('face_extraction_error')->nullable()->after('mime_type');
            $table->timestamp('face_extracted_at')->nullable()->after('face_extraction_error');
        });
    }

    public function down(): void
    {
        Schema::table('pendataan_documents', function (Blueprint $table) {
            $table->dropColumn(['face_extraction_error', 'face_extracted_at']);
        });
    }
};
