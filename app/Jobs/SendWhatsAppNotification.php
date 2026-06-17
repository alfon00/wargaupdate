<?php

namespace App\Jobs;

use App\Models\Application;
use App\Services\WahaNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsAppNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $applicationId) {}

    public function handle(WahaNotificationService $waha): void
    {
        $application = Application::with(['resident.household.rtProfile', 'serviceType'])
            ->find($this->applicationId);

        if ($application) {
            $waha->notifyApplicationStatus($application);
        }
    }
}
