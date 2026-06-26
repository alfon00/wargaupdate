<?php

use App\Models\GeneratedLetter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generated_letters', function (Blueprint $table) {
            $table->string('verification_token', 64)->nullable()->unique()->after('letter_number');
        });

        GeneratedLetter::query()
            ->whereNotNull('issued_at')
            ->whereNull('verification_token')
            ->each(function (GeneratedLetter $letter) {
                $letter->update(['verification_token' => (string) Str::uuid()]);
            });
    }

    public function down(): void
    {
        Schema::table('generated_letters', function (Blueprint $table) {
            $table->dropColumn('verification_token');
        });
    }
};
