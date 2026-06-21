<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    /** @return list<string> */
    public static function defaultRequiredFields(): array
    {
        return config('kelurahan.layanan_persyaratan.berkas_surat', ['KK', 'KTP']);
    }

    /** @return list<array{code: string, name: string, description: string, required_fields: list<string>}> */
    public static function catalog(): array
    {
        $requiredFields = self::defaultRequiredFields();

        return [
            [
                'code' => 'surat_tidak_mampu',
                'name' => 'Surat Keterangan Tidak Mampu (SKTM)',
                'description' => 'Keterangan tidak mampu untuk bantuan sosial atau beasiswa.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_usaha',
                'name' => 'Surat Keterangan Usaha (SKU)',
                'description' => 'Keterangan usaha untuk perizinan atau administrasi usaha mikro.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_domisili',
                'name' => 'Surat Keterangan Domisili',
                'description' => 'Keterangan domisili untuk keperluan administrasi umum.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_pengantar_kk',
                'name' => 'Surat Keterangan Pengurusan KK',
                'description' => 'Keterangan untuk pengurusan Kartu Keluarga di Dukcapil — bukan penerbitan KK di portal ini.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_pengantar_ktp',
                'name' => 'Surat Keterangan Pengurusan KTP',
                'description' => 'Keterangan untuk pembuatan atau perpanjangan KTP-el di Dukcapil.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_pengantar_skck',
                'name' => 'Surat Keterangan Pengurusan SKCK',
                'description' => 'Keterangan untuk pengajuan SKCK ke Polres/Polsek setempat.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_pengantar_umum',
                'name' => 'Surat Keterangan Umum',
                'description' => 'Keterangan untuk keperluan administrasi lain yang tidak tercantum di atas.',
                'required_fields' => $requiredFields,
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::catalog() as $svc) {
            ServiceType::updateOrCreate(
                ['code' => $svc['code']],
                [
                    'name' => $svc['name'],
                    'description' => $svc['description'],
                    'required_fields' => $svc['required_fields'],
                    'is_active' => true,
                ]
            );
        }
    }
}
