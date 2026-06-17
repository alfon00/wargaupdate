<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rt_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_profile_id')->constrained('rt_profiles')->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('judul');
            $table->text('ringkasan')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('foto_path')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_published', 'published_at']);
        });

        Schema::table('rt_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('rt_profiles', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('logo_path');
            }
            if (! Schema::hasColumn('rt_profiles', 'facebook_url')) {
                $table->string('facebook_url')->nullable()->after('instagram_url');
            }
            if (! Schema::hasColumn('rt_profiles', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('facebook_url');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rt_publications');

        Schema::table('rt_profiles', function (Blueprint $table) {
            foreach (['instagram_url', 'facebook_url', 'youtube_url'] as $column) {
                if (Schema::hasColumn('rt_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
