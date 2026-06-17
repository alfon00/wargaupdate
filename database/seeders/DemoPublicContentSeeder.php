<?php

namespace Database\Seeders;

use App\Enums\RtPublicationType;
use App\Models\RtProfile;
use App\Models\RtPublication;
use Illuminate\Database\Seeder;

class DemoPublicContentSeeder extends Seeder
{
    public function run(): void
    {
        RtProfile::inauga()->each(function (RtProfile $profile) {
            if (! filled($profile->jam_layanan)) {
                $profile->update([
                    'jam_layanan' => config('kelurahan.kontak_jam_default'),
                ]);
            }
        });

        $rt = RtProfile::inauga()->withRegisteredStaff()->first();
        if (! $rt || RtPublication::where('type', RtPublicationType::Kegiatan)->exists()) {
            return;
        }

        RtPublication::create([
            'rt_profile_id' => $rt->id,
            'type' => RtPublicationType::Kegiatan,
            'judul' => 'Kerja bakti lingkungan',
            'ringkasan' => 'Gotong royong membersihkan saluran dan trotoar RT.',
            'tanggal' => now()->addDays(7),
            'lokasi' => 'Wilayah '.$rt->displayName(),
            'is_published' => true,
            'published_at' => now(),
        ]);

        RtPublication::create([
            'rt_profile_id' => $rt->id,
            'type' => RtPublicationType::Pengumuman,
            'judul' => 'Jam layanan sekretariat RT',
            'ringkasan' => 'Sekretariat RT buka Senin–Jumat pukul 08.00–14.00 WIT untuk layanan surat pengantar.',
            'tanggal' => now(),
            'expires_at' => null,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
