<?php

namespace App\Support;

final class TrackContent
{
    public static function heroLead(): string
    {
        return 'Pantau proses surat pengantar RT. Gunakan nomor dari halaman sukses pengajuan atau notifikasi WhatsApp.';
    }

    public static function formLead(): string
    {
        return 'Masukkan nomor permohonan Anda. Nomor diberikan setelah pengajuan atau melalui notifikasi WhatsApp.';
    }
}
