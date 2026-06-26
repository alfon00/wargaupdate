<?php

use App\Support\LetterTemplateSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        LetterTemplateSeeder::refreshAll();
    }

    public function down(): void
    {
        LetterTemplateSeeder::refreshAll();
    }
};
