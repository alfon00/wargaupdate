<?php

namespace App\Console\Commands;

use App\Models\LetterTemplate;
use App\Models\ServiceType;
use App\Support\LetterTemplateSeeder;
use App\Support\SuratPengantarTemplate;
use Illuminate\Console\Command;

class SeedLetterTemplate extends Command
{
    protected $signature = 'lw:seed-letter-template
                            {code? : Kode layanan, mis. surat_usaha}
                            {--all : Perbarui semua template layanan aktif}';

    protected $description = 'Perbarui template HTML surat untuk jenis layanan';

    public function handle(): int
    {
        if ($this->option('all')) {
            $count = LetterTemplateSeeder::refreshAll();
            $this->info("{$count} template surat diperbarui.");

            return self::SUCCESS;
        }

        $code = $this->argument('code');
        if (! $code) {
            $this->error('Berikan kode layanan atau gunakan --all.');

            return self::FAILURE;
        }

        $service = ServiceType::where('code', $code)->first();
        if (! $service) {
            $this->error("Layanan dengan kode \"{$code}\" tidak ditemukan.");

            return self::FAILURE;
        }

        $body = SuratPengantarTemplate::bodyForServiceCode($code);
        $template = LetterTemplate::where('service_type_id', $service->id)->first();

        if ($template) {
            $template->update(['body_html' => $body, 'is_active' => true]);
            $this->info("Template \"{$service->name}\" diperbarui (id {$template->id}).");
        } else {
            $template = LetterTemplate::create([
                'service_type_id' => $service->id,
                'name' => 'Template '.$service->name,
                'body_html' => $body,
                'is_active' => true,
            ]);
            $this->info("Template \"{$service->name}\" dibuat (id {$template->id}).");
        }

        return self::SUCCESS;
    }
}
