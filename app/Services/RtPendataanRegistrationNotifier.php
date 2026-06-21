<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Resident;

class RtPendataanRegistrationNotifier
{
    public function __construct(
        private readonly WahaNotificationService $waha,
    ) { }

    public function notifyAfterRtEntry(Resident $resident): ?NotificationLog
    {
        $resident->loadMissing(['household.rtProfile']);
        $head = $resident->headOfHousehold() ?? $resident;
        $head->loadMissing(['household.rtProfile']);
        $rt = $head->household?->rtProfile;

        if (! $rt) {
            return null;
        }

        return $this->waha->notifyPendataanRegisteredByRt($head, $rt);
    }

    public function flashSuffix(?NotificationLog $log): string
    {
        if (! $log) {
            return '';
        }

        return match ($log->status) {
            'sent' => ' Notifikasi WhatsApp terkirim ke '.$this->maskPhone($log->phone).'.',
            'skipped' => ' Notifikasi WhatsApp tidak dikirim: '.($log->error_message ?? 'nomor tidak tersedia').'.',
            'failed' => ' Notifikasi WhatsApp gagal: '.($log->error_message ?? 'periksa koneksi WAHA').'.',
            default => '',
        };
    }

    protected function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? $phone;
        if (strlen($digits) <= 4) {
            return $phone;
        }

        return str_repeat('•', max(0, strlen($digits) - 4)).substr($digits, -4);
    }
}
