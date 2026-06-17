<?php

namespace App\Support;

final class ContactContent
{
    public static function introTitle(): string
    {
        return 'Kirim laporan ke pengurus RT';
    }

    public static function introLead(): string
    {
        return 'Satu formulir untuk kendala layanan surat, pendataan warga, masalah teknis portal, pengaduan lingkungan, dan pertanyaan umum.';
    }

    public static function formLead(): string
    {
        return 'Pilih RT dan jenis pesan, lalu isi uraian. Pengurus menindaklanjuti melalui nomor kontak yang Anda berikan.';
    }

    /** @return list<array{title: string, desc: string}> */
    public static function benefits(): array
    {
        return [
            [
                'title' => 'Ditindaklanjuti pengurus RT',
                'desc' => 'Laporan masuk ke panel pengurus wilayah Anda.',
            ],
            [
                'title' => 'Notifikasi WhatsApp',
                'desc' => 'Konfirmasi pengiriman dan pembaruan status dikirim ke nomor Anda.',
            ],
            [
                'title' => 'Mudah dari HP atau komputer',
                'desc' => 'Kirim laporan kapan saja tanpa datang ke sekretariat.',
            ],
        ];
    }
}
