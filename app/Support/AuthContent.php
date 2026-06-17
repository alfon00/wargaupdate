<?php

namespace App\Support;

final class AuthContent
{
    public static function introTitle(): string
    {
        return 'Panel pengurus terlindungi';
    }

    public static function introLead(): string
    {
        return 'Akses panel untuk mengelola permohonan surat, verifikasi pendataan, dan data warga — hanya untuk pengurus terdaftar.';
    }

    public static function formLead(): string
    {
        return 'Gunakan email dan kata sandi akun pengurus RT, kelurahan, atau admin.';
    }

    public static function loginNote(): string
    {
        return 'Hanya akun pengurus RT, kelurahan, atau admin yang terdaftar. Bukan situs Dukcapil, Kemendagri, atau perbankan.';
    }

    /** @return list<array{title: string, desc: string}> */
    public static function securityBenefits(): array
    {
        return [
            [
                'title' => 'Sesi terenkripsi',
                'desc' => 'Koneksi HTTPS untuk melindungi kredensial masuk.',
            ],
            [
                'title' => 'Akses sesuai peran',
                'desc' => 'Panel RT, kelurahan, atau admin — menu disesuaikan peran.',
            ],
            [
                'title' => 'Data terbatas pengurus',
                'desc' => 'Data warga dan permohonan hanya untuk akun terdaftar.',
            ],
        ];
    }
}
