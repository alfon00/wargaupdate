<?php

namespace App\Observers;

use App\Jobs\SendWhatsAppNotification;
use App\Models\Application;

class ApplicationObserver
{
    public function created(Application $application): void
    {
        if ($application->status->notifyEvent()) {
            SendWhatsAppNotification::dispatchSync($application->id);
        }
    }

    public function updated(Application $application): void
    {
        if (! $application->wasChanged('status')) {
            return;
        }

        if ($application->status->notifyEvent()) {
            SendWhatsAppNotification::dispatchSync($application->id);
        }
    }
}
