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
                ->update([
                    'name' => $svc['name'],
                    'description' => $svc['description'],
                ]);
        }
    }

    public function down(): void
    {
        $previous = [
            'surat_tidak_mampu' => [
                'name' => 'Surat Pengantar SKTM',
                'description' => 'Surat pengantar dari RT untuk pengurusan SKTM (tidak mampu) di instansi berwenang.',
            ],
            'surat_usaha' => [
                'name' => 'Surat Pengantar Usaha (SKU)',
                'description' => 'Surat pengantar dari RT untuk pengurusan keterangan usaha atau perizinan usaha mikro.',
            ],
            'surat_domisili' => [
                'name' => 'Surat Pengantar Domisili',
                'description' => 'Surat pengantar dari RT untuk keperluan administrasi domisili di kelurahan atau instansi terkait.',
            ],
            'surat_pengantar_kk' => [
                'name' => 'Surat Pengantar KK',
                'description' => 'Surat pengantar dari RT untuk pengurusan Kartu Keluarga di Dukcapil — bukan penerbitan KK di portal ini.',
            ],
            'surat_pengantar_ktp' => [
                'name' => 'Surat Pengantar KTP',
                'description' => 'Surat pengantar dari RT untuk pembuatan atau perpanjangan KTP-el di Dukcapil.',
            ],
            'surat_pengantar_skck' => [
                'name' => 'Surat Pengantar SKCK',
                'description' => 'Surat pengantar dari RT untuk pengajuan SKCK ke Polres/Polsek setempat.',
            ],
            'surat_pengantar_umum' => [
                'name' => 'Surat Pengantar Umum',
                'description' => 'Surat pengantar dari RT untuk keperluan administrasi lain yang tidak tercantum di atas.',
            ],
        ];

        foreach ($previous as $code => $data) {
            ServiceType::query()
                ->where('code', $code)
                ->update($data);
        }
    }
};
