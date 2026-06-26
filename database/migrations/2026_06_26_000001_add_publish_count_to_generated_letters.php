<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generated_letters', function (Blueprint $table) {
            $table->unsignedSmallInteger('publish_count')->default(0)->after('issued_at');
        });

        DB::table('generated_letters')
            ->whereNotNull('issued_at')
            ->update(['publish_count' => 1]);
    }

    public function down(): void
    {
        Schema::table('generated_letters', function (Blueprint $table) {
            $table->dropColumn('publish_count');
        });
    }
};
