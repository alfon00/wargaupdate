<?php

use App\Models\Application;
use App\Models\GeneratedLetter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('letter_verification_token', 64)->nullable()->unique()->after('application_number');
        });

        GeneratedLetter::query()
            ->whereNotNull('verification_token')
            ->whereNotNull('issued_at')
            ->with('application')
            ->each(function (GeneratedLetter $letter) {
                $application = $letter->application;
                if (! $application || filled($application->letter_verification_token)) {
                    return;
                }

                $application->update(['letter_verification_token' => $letter->verification_token]);
            });

        Application::query()
            ->whereNull('letter_verification_token')
            ->whereHas('generatedLetter', fn ($q) => $q->whereNotNull('issued_at'))
            ->each(function (Application $application) {
                $application->update(['letter_verification_token' => (string) Str::uuid()]);
            });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('letter_verification_token');
        });
    }
};
