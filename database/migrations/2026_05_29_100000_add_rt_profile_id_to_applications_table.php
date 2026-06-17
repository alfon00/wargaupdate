<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('rt_profile_id')
                ->nullable()
                ->after('resident_id')
                ->constrained('rt_profiles')
                ->nullOnDelete();
        });

        DB::table('applications')
            ->whereNull('rt_profile_id')
            ->orderBy('id')
            ->chunkById(100, function ($applications) {
                foreach ($applications as $application) {
                    $rtProfileId = DB::table('residents')
                        ->join('households', 'residents.household_id', '=', 'households.id')
                        ->where('residents.id', $application->resident_id)
                        ->value('households.rt_profile_id');

                    if ($rtProfileId) {
                        DB::table('applications')
                            ->where('id', $application->id)
                            ->update(['rt_profile_id' => $rtProfileId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rt_profile_id');
        });
    }
};
