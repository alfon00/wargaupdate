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
                'name' => 'Surat Pengantar SKTM',
                'description' => 'Surat pengantar dari RT untuk pengurusan SKTM (tidak mampu) di instansi berwenang.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_usaha',
                'name' => 'Surat Pengantar Usaha (SKU)',
                'description' => 'Surat pengantar dari RT untuk pengurusan keterangan usaha atau perizinan usaha mikro.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_domisili',
                'name' => 'Surat Pengantar Domisili',
                'description' => 'Surat pengantar dari RT untuk keperluan administrasi domisili di kelurahan atau instansi terkait.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_pengantar_kk',
                'name' => 'Surat Pengantar KK',
                'description' => 'Surat pengantar dari RT untuk pengurusan Kartu Keluarga di Dukcapil — bukan penerbitan KK di portal ini.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_pengantar_ktp',
                'name' => 'Surat Pengantar KTP',
                'description' => 'Surat pengantar dari RT untuk pembuatan atau perpanjangan KTP-el di Dukcapil.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_pengantar_skck',
                'name' => 'Surat Pengantar SKCK',
                'description' => 'Surat pengantar dari RT untuk pengajuan SKCK ke Polres/Polsek setempat.',
                'required_fields' => $requiredFields,
            ],
            [
                'code' => 'surat_pengantar_umum',
                'name' => 'Surat Pengantar Umum',
                'description' => 'Surat pengantar dari RT untuk keperluan administrasi lain yang tidak tercantum di atas.',
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
