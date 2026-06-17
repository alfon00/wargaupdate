<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rt_publications', function (Blueprint $table) {
            $table->date('expires_at')->nullable()->after('published_at');
            $table->index(['type', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('rt_publications', function (Blueprint $table) {
            $table->dropIndex(['type', 'expires_at']);
            $table->dropColumn('expires_at');
        });
    }
};
