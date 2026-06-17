<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelurahan_officials', function (Blueprint $table) {
            $table->id();
            $table->string('role', 32)->unique();
            $table->string('jabatan')->nullable();
            $table->string('nama')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('alamat_kantor')->nullable();
            $table->string('jam_layanan')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->timestamps();
        });

        $lurah = config('kelurahan.lurah', []);
        DB::table('kelurahan_officials')->insert([
            'role' => 'lurah',
            'jabatan' => $lurah['jabatan'] ?? 'Lurah Kelurahan Inauga',
            'nama' => $lurah['nama'] ?? null,
            'photo_path' => null,
            'telepon' => $lurah['telepon'] ?? null,
            'whatsapp' => $lurah['whatsapp'] ?? null,
            'email' => $lurah['email'] ?? null,
            'alamat_kantor' => $lurah['alamat_kantor'] ?? null,
            'jam_layanan' => $lurah['jam_layanan'] ?? null,
            'visi' => $lurah['visi'] ?? null,
            'misi' => $lurah['misi'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kelurahan_officials');
    }
};
