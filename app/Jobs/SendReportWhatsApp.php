<?php

namespace App\Jobs;

use App\Models\CitizenReport;
use App\Services\WahaNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendReportWhatsApp implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $reportId,
        public string $event,
    ) {}

    public function handle(WahaNotificationService $waha): void
    {
        $report = CitizenReport::with('rtProfile')->find($this->reportId);
        if (! $report) {
            return;
        }

        match ($this->event) {
            'report_submitted' => $waha->notifyReportSubmitted($report),
            'report_status_updated' => $waha->notifyReportStatusUpdated($report),
            default => null,
        };
    }
}
