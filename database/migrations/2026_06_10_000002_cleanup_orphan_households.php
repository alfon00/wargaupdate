<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $orphanIds = DB::table('households')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('residents')
                    ->whereColumn('residents.household_id', 'households.id');
            })
            ->pluck('id');

        if ($orphanIds->isEmpty()) {
            return;
        }

        DB::table('pendataan_documents')
            ->whereIn('household_id', $orphanIds)
            ->delete();

        DB::table('households')
            ->whereIn('id', $orphanIds)
            ->delete();
    }

    public function down(): void
    {
        // Data cleanup cannot be reversed.
    }
};
