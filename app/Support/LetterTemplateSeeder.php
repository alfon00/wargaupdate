<?php

namespace App\Support;

use App\Models\LetterTemplate;
use App\Models\ServiceType;

class LetterTemplateSeeder
{
    public static function refreshAll(): int
    {
        $count = 0;

        ServiceType::query()->where('is_active', true)->orderBy('code')->each(function (ServiceType $service) use (&$count) {
            $body = SuratPengantarTemplate::bodyForServiceCode($service->code);

            $template = LetterTemplate::query()
                ->where('service_type_id', $service->id)
                ->first();

            if ($template) {
                $template->update(['body_html' => $body, 'is_active' => true]);
            } else {
                LetterTemplate::create([
                    'service_type_id' => $service->id,
                    'name' => 'Template '.$service->name,
                    'body_html' => $body,
                    'is_active' => true,
                ]);
            }

            $count++;
        });

        return $count;
    }
}
