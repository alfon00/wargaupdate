<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rt_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('rt_profiles', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('rt_profiles', 'visi')) {
                $table->text('visi')->nullable()->after('alamat_kantor');
            }
            if (! Schema::hasColumn('rt_profiles', 'misi')) {
                $table->text('misi')->nullable()->after('visi');
            }
            if (! Schema::hasColumn('rt_profiles', 'phone')) {
                $table->string('phone', 20)->nullable()->after('misi');
            }
            if (! Schema::hasColumn('rt_profiles', 'whatsapp')) {
                $table->string('whatsapp', 20)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('rt_profiles', 'jam_layanan')) {
                $table->string('jam_layanan')->nullable()->after('whatsapp');
            }
            if (! Schema::hasColumn('rt_profiles', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('jam_layanan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rt_profiles', function (Blueprint $table) {
            $columns = ['slug', 'visi', 'misi', 'phone', 'whatsapp', 'jam_layanan', 'logo_path'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('rt_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
