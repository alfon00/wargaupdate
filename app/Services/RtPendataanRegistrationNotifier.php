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
        $rt = $resident->household?->rtProfile ?? $head->household?->rtProfile;

        if (! $rt) {
            return null;
        }

        $recipient = $this->resolveNotificationRecipient($resident, $head);
        $registeredMember = ($recipient && $recipient->is($head) && ! $resident->is_head_of_family)
            ? $resident
            : null;

        return $this->waha->notifyPendataanRegisteredByRt(
            $recipient ?? $head,
            $rt,
            $registeredMember,
        );
    }

    protected function resolveNotificationRecipient(Resident $resident, Resident $head): ?Resident
    {
        if ($resident->is_head_of_family && filled($resident->whatsappNotificationPhone())) {
            return $resident;
        }

        if (! $resident->is_head_of_family && filled($head->whatsappNotificationPhone())) {
            return $head;
        }

        if (! $resident->is_head_of_family && filled($resident->whatsappNotificationPhone())) {
            return $resident;
        }

        return null;
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
