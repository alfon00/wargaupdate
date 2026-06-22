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
        return 'Akses panel untuk pengurus RT atau kelurahan — mengelola permohonan surat, verifikasi pendataan, dan data warga.';
    }

    public static function formLead(): string
    {
        return 'Gunakan email dan kata sandi akun pengurus RT atau kelurahan.';
    }

    public static function loginNote(): string
    {
        return 'Hanya akun pengurus RT atau kelurahan yang terdaftar. Bukan situs Dukcapil, Kemendagri, atau perbankan.';
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
                'desc' => 'Panel RT atau kelurahan — menu disesuaikan peran akun.',
            ],
            [
                'title' => 'Data terbatas pengurus',
                'desc' => 'Data warga dan permohonan hanya untuk akun terdaftar.',
            ],
        ];
    }
}
