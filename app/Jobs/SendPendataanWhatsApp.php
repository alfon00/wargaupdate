<?php

namespace App\Jobs;

use App\Models\Resident;
use App\Services\WahaNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPendataanWhatsApp implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $residentId,
        public string $event,
        public ?string $notes = null,
    ) {}

    public function handle(WahaNotificationService $waha): void
    {
        $resident = Resident::with(['household.rtProfile'])->find($this->residentId);
        if (! $resident) {
            return;
        }

        $head = $resident->headOfHousehold() ?? $resident;
        $head->loadMissing(['household.rtProfile']);
        $rt = $head->household?->rtProfile;
        if (! $rt) {
            return;
        }

        match ($this->event) {
            'pendataan_submitted' => $waha->notifyPendataanSubmitted($head, $rt),
            'pendataan_verified' => $waha->notifyPendataanVerified($head, $rt),
            'pendataan_registered_by_rt' => $waha->notifyPendataanRegisteredByRt($head, $rt),
            'pendataan_rejected' => $waha->notifyPendataanRejected($head, $rt, $this->notes ?? ''),
            default => null,
        };
    }
}
