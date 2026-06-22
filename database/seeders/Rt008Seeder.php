<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Household;
use App\Models\LetterTemplate;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\ServiceType;
use App\Models\User;
use App\Support\SuratPengantarTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class Rt008Seeder extends Seeder
{
    public function run(): void
    {
        $rt = RtProfile::create([
            'rt_number' => '008',
            'rw_number' => '005',
            'kelurahan' => 'Kelurahan Contoh',
            'kecamatan' => 'Kecamatan Contoh',
            'kota' => 'Kota Contoh',
            'provinsi' => 'Provinsi Contoh',
            'ketua_rt' => 'Bapak Ketua RT',
            'ketua_rw' => 'Bapak Ketua RW',
            'sekretaris_rt' => 'Ibu Sekretaris RT',
            'alamat_kantor' => 'Jl. Warga RT-008 No. 1',
        ]);

        $services = [
            ['code' => 'surat_domisili', 'name' => 'Surat Pengantar Domisili'],
            ['code' => 'surat_tidak_mampu', 'name' => 'Surat Keterangan Tidak Mampu'],
            ['code' => 'surat_usaha', 'name' => 'Surat Keterangan Usaha'],
            ['code' => 'surat_pengantar_umum', 'name' => 'Surat Pengantar Umum'],
        ];

        foreach ($services as $svc) {
            $type = ServiceType::create([
                'code' => $svc['code'],
                'name' => $svc['name'],
                'description' => 'Layanan administrasi '.$svc['name'].' RT-008',
                'is_active' => true,
            ]);

            LetterTemplate::create([
                'service_type_id' => $type->id,
                'name' => 'Template '.$svc['name'],
                'body_html' => SuratPengantarTemplate::bodyHtml(),
                'is_active' => true,
            ]);
        }

        $household = Household::create([
            'rt_profile_id' => $rt->id,
            'family_card_number' => '3201010101010001',
            'house_number' => '12',
            'address' => 'Jl. Melati RT-008',
            'status' => 'aktif',
        ]);

        $resident = Resident::create([
            'household_id' => $household->id,
            'nik' => '3201010101010001',
            'name' => 'Warga Contoh',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-15',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'occupation' => 'Karyawan',
            'marital_status' => 'Kawin',
            'phone' => '081234567890',
            'is_head_of_family' => true,
            'relationship_to_head' => 'Kepala Keluarga',
        ]);

        User::create([
            'name' => 'Admin RT-008',
            'email' => 'admin@layananwarga.my.id',
            'password' => Hash::make('Rt008Admin!'),
            'role' => UserRole::KetuaRt,
            'phone' => '081234567890',
        ]);
    }
}
