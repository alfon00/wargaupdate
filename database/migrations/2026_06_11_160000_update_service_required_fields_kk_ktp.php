<?php

use App\Models\ServiceType;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $requiredFields = ServiceCatalogSeeder::defaultRequiredFields();

        foreach (ServiceCatalogSeeder::catalog() as $svc) {
            ServiceType::query()
                ->where('code', $svc['code'])
                ->update(['required_fields' => $requiredFields]);
        }
    }

    public function down(): void
    {
        $previous = [
            'surat_tidak_mampu' => [
                'KTP dan KK warga terdata untuk pengajuan surat pengantar RT',
                'Surat permohonan',
                'Data pendukung keperluan SKTM di instansi berwenang',
            ],
            'surat_usaha' => [
                'KTP dan KK warga terdata untuk pengajuan surat pengantar RT',
                'Foto usaha (jika ada)',
            ],
            'surat_domisili' => ['KTP dan KK warga terdata untuk pengajuan surat pengantar RT'],
            'surat_pengantar_kk' => [
                'KTP dan KK warga terdata untuk pengajuan surat pengantar RT',
                'KK lama (jika perubahan data)',
            ],
            'surat_pengantar_ktp' => [
                'KTP dan KK warga terdata untuk pengajuan surat pengantar RT',
                'KTP lama (jika perpanjangan)',
            ],
            'surat_pengantar_skck' => [
                'KTP dan KK warga terdata untuk pengajuan surat pengantar RT',
                'Pas foto terbaru',
            ],
            'surat_pengantar_umum' => ['KTP dan KK warga terdata untuk pengajuan surat pengantar RT'],
        ];

        foreach ($previous as $code => $requiredFields) {
            ServiceType::query()
                ->where('code', $code)
                ->update(['required_fields' => $requiredFields]);
        }
    }
};
