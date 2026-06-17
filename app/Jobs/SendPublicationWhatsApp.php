<?php

namespace App\Jobs;

use App\Enums\DomicileStatus;
use App\Models\Resident;
use App\Models\RtPublication;
use App\Services\WahaNotificationService;
use App\Support\PhoneNormalizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPublicationWhatsApp implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $publicationId,
    ) {}

    /**
     * @return array{sent: int, skipped: int, failed: int}
     */
    public function handle(WahaNotificationService $waha): array
    {
        $publication = RtPublication::with('rtProfile')->find($this->publicationId);
        if (! $publication || ! $publication->rtProfile) {
            return ['sent' => 0, 'skipped' => 0, 'failed' => 0];
        }

        $profileIds = \App\Models\RtProfile::profileIdsForRtNumber($publication->rtProfile->rt_number);

        $residents = Resident::query()
            ->whereHas('household', fn ($q) => $q->whereIn('rt_profile_id', $profileIds))
            ->where('domicile_status', DomicileStatus::Aktif)
            ->where('whatsapp_notify', true)
            ->get();

        $summary = ['sent' => 0, 'skipped' => 0, 'failed' => 0];
        $sentPhones = [];

        foreach ($residents as $resident) {
            $phone = $resident->whatsappNotificationPhone();
            if (! $phone) {
                $summary['skipped']++;

                continue;
            }

            $phoneKey = PhoneNormalizer::digits($phone);
            if (isset($sentPhones[$phoneKey])) {
                $summary['skipped']++;

                continue;
            }

            $sentPhones[$phoneKey] = true;
            $log = $waha->notifyPublication($resident, $publication);
            match ($log->status) {
                'sent' => $summary['sent']++,
                'skipped' => $summary['skipped']++,
                default => $summary['failed']++,
            };
        }

        return $summary;
    }
}
