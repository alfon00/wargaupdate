<?php

use App\Models\ServiceType;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (ServiceCatalogSeeder::catalog() as $svc) {
            ServiceType::query()
                ->where('code', $svc['code'])
                ->update(['required_fields' => $svc['required_fields']]);
        }
    }

    public function down(): void
    {
        $legacy = [
            'surat_tidak_mampu' => ['Fotokopi KTP', 'Fotokopi KK', 'Surat permohonan'],
            'surat_usaha' => ['Fotokopi KTP', 'Fotokopi KK', 'Foto usaha (jika ada)'],
            'surat_domisili' => ['Fotokopi KTP', 'Fotokopi KK'],
            'surat_pengantar_kk' => ['Fotokopi KTP', 'Fotokopi KK', 'KK lama (jika perubahan data)'],
            'surat_pengantar_ktp' => ['Fotokopi KTP', 'Fotokopi KK', 'KTP lama (jika perpanjangan)'],
            'surat_pengantar_skck' => ['Fotokopi KTP', 'Fotokopi KK', 'Pas foto terbaru'],
            'surat_pengantar_umum' => ['Fotokopi KTP', 'Fotokopi KK'],
        ];

        foreach ($legacy as $code => $requiredFields) {
            ServiceType::query()
                ->where('code', $code)
                ->update(['required_fields' => $requiredFields]);
        }
    }
};
