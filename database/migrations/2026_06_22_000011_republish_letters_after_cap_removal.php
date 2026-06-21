<?php

use App\Models\Application;
use App\Models\GeneratedLetter;
use App\Services\LetterGeneratorService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        GeneratedLetter::query()->each(function (GeneratedLetter $letter) {
            $fields = $letter->letter_fields;
            if (! is_array($fields) || ! array_key_exists('cap_rt_gambar', $fields)) {
                return;
            }

            unset($fields['cap_rt_gambar']);
            $letter->update(['letter_fields' => $fields]);
        });

        /** @var LetterGeneratorService $generator */
        $generator = app(LetterGeneratorService::class);

        Application::query()
            ->whereHas('generatedLetter')
            ->with('generatedLetter')
            ->each(function (Application $application) use ($generator) {
                try {
                    $generator->republish($application);
                } catch (\Throwable $e) {
                    Log::warning('Gagal menerbitkan ulang surat setelah penghapusan cap RT.', [
                        'application_id' => $application->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Tidak dapat mengembalikan cap atau PDF lama.
    }
};
