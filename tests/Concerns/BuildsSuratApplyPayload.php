<?php

namespace Tests\Concerns;

use Illuminate\Http\UploadedFile;

trait BuildsSuratApplyPayload
{
    /** @return array<string, mixed> */
    protected function pemohonPayload(int $rtProfileId, string $nik = '3201010101010001'): array
    {
        return [
            'rt_profile_id' => $rtProfileId,
            'nik' => $nik,
            'name' => 'Warga Uji',
            'phone' => '081234567890',
            'whatsapp_notify' => '1',
        ];
    }

    /** @param  list<string>  $labels */
    protected function documentsPayload(array $labels = ['KK', 'KTP']): array
    {
        $documents = [];

        foreach ($labels as $index => $label) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $label) ?? 'doc');
            $documents[$index] = UploadedFile::fake()->create($slug.'_'.$index.'.pdf', 100, 'application/pdf');
        }

        return ['documents' => $documents];
    }

    /** @return array<string, mixed> */
    protected function applyStorePayload(
        int $rtProfileId,
        string $nik = '3201010101010001',
        string $purpose = 'Keperluan uji',
        bool $withDocuments = true,
    ): array {
        $payload = [
            ...$this->pemohonPayload($rtProfileId, $nik),
            'purpose' => $purpose,
        ];

        if ($withDocuments) {
            $payload = [...$payload, ...$this->documentsPayload()];
        }

        return $payload;
    }
}
