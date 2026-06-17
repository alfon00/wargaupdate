<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generated_letters', function (Blueprint $table) {
            $table->json('letter_fields')->nullable()->after('letter_number');
            $table->string('signature_path')->nullable()->after('letter_fields');
            $table->timestamp('signed_at')->nullable()->after('signature_path');
            $table->foreignId('signed_by')->nullable()->after('signed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('generated_letters', function (Blueprint $table) {
            $table->dropForeign(['signed_by']);
            $table->dropColumn(['letter_fields', 'signature_path', 'signed_at', 'signed_by']);
        });
    }
};
