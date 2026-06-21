<?php

namespace App\Services;

use App\Models\Application;
use App\Models\GeneratedLetter;
use App\Models\LetterTemplate;
use App\Support\LetterFieldSchema;
use App\Support\LetterKopFields;
use App\Support\LetterPdfStyles;
use App\Support\SignatureStorage;
use App\Models\RtProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LetterGeneratorService
{
    /**
     * @param  array<string, string>  $fields
     */
    public function generate(
        Application $application,
        array $fields = [],
        ?string $signatureDataUri = null,
        ?int $signedBy = null,
    ): GeneratedLetter {
        $application->loadMissing(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType', 'generatedLetter']);

        $template = LetterTemplate::where('service_type_id', $application->service_type_id)
            ->where('is_active', true)
            ->firstOrFail();

        $existingLetter = $application->generatedLetter;
        $signaturePath = null;
        if ($signatureDataUri && ! SignatureStorage::isBlank($signatureDataUri)) {
            if ($existingLetter?->signature_path) {
                Storage::disk('local')->delete($existingLetter->signature_path);
            }
            $signaturePath = SignatureStorage::store($signatureDataUri, $application->id);
        } elseif ($existingLetter?->signature_path && Storage::disk('local')->exists($existingLetter->signature_path)) {
            $signaturePath = $existingLetter->signature_path;
        }

        $merged = $this->mergeFields($application, $fields, $signatureDataUri, $signaturePath);
        $html = $this->renderTemplate($template->body_html, $merged);

        $filename = 'letters/'.Str::slug($application->application_number).'-'.now()->format('YmdHis').'.pdf';
        $pdf = Pdf::loadHTML($html)->setPaper('A4');
        Storage::disk('local')->put($filename, $pdf->output());

        $snapshot = collect($merged)
            ->except(['ttd_gambar', 'cap_rt_gambar', 'ttd_tanda_cap', 'nomor_surat', 'tanggal'])
            ->all();

        return GeneratedLetter::updateOrCreate(
            ['application_id' => $application->id],
            [
                'letter_template_id' => $template->id,
                'file_path' => $filename,
                'letter_number' => $merged['nomor_surat'],
                'letter_fields' => $snapshot,
                'signature_path' => $signaturePath ?? $existingLetter?->signature_path,
                'signed_at' => $signedBy ? now() : ($existingLetter?->signed_at),
                'signed_by' => $signedBy ?? $existingLetter?->signed_by,
                'issued_at' => now(),
            ]
        );
    }

    /**
     * @param  array<string, string>  $fields
     */
    public function previewHtml(Application $application, array $fields = [], ?string $signatureDataUri = null): string
    {
        $application->loadMissing(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType', 'generatedLetter']);

        $template = LetterTemplate::where('service_type_id', $application->service_type_id)
            ->where('is_active', true)
            ->firstOrFail();

        $existingLetter = $application->generatedLetter;
        $existingSignaturePath = $existingLetter?->signature_path;

        $merged = $this->mergeFields(
            $application,
            $fields,
            $signatureDataUri,
            $existingSignaturePath && Storage::disk('local')->exists($existingSignaturePath)
                ? $existingSignaturePath
                : null,
        );

        return $this->renderTemplate($template->body_html, $merged);
    }

    public function republish(Application $application): GeneratedLetter
    {
        $application->loadMissing(['generatedLetter']);

        $storedFields = $application->generatedLetter?->letter_fields ?? [];
        if (! is_array($storedFields)) {
            $storedFields = [];
        }

        $formFields = $application->form_data['letter']['fields'] ?? [];
        if (! is_array($formFields)) {
            $formFields = [];
        }

        $fields = array_merge(
            $storedFields,
            $formFields,
        );

        foreach ($fields as $key => $value) {
            $fields[$key] = is_string($value) ? $value : (string) $value;
        }

        return $this->generate($application, $fields);
    }

    /**
     * @param  array<string, string>  $fields
     * @return array<string, string>
     */
    protected function mergeFields(
        Application $application,
        array $fields,
        ?string $signatureDataUri,
        ?string $signatureFilePath = null,
    ): array {
        $merged = array_merge(
            LetterFieldSchema::defaultValues($application),
            LetterKopFields::forApplication($application),
            $fields,
        );
        $letterNumber = $this->resolveLetterNumber($application, $merged);

        $merged['nomor_surat'] = $letterNumber;
        $merged['nomor_surat_baris'] = LetterKopFields::nomorSuratBaris(
            $letterNumber,
            $merged['rt_nomor'] ?? '',
            $merged['rw_nomor'] ?? '',
        );
        $merged['tanggal'] = now()->format('d-m-Y');
        $merged['ttd_gambar'] = $this->resolveSignatureImgTag($signatureDataUri, $signatureFilePath);
        $merged['cap_rt_gambar'] = $this->resolveStampImgTag($application, $signatureDataUri, $signatureFilePath);
        $merged['ttd_tanda_cap'] = $this->resolveTtdSignBlock(
            $application,
            $signatureDataUri,
            $signatureFilePath,
        );
        $merged['logo_rt'] = LetterFieldSchema::logoImgTag($application);
        if (! filled($merged['logo_kop'] ?? null)) {
            $merged['logo_kop'] = LetterKopFields::kopLogoImgTag();
        }

        return $merged;
    }

    protected function resolveSignatureImgTag(?string $signatureDataUri, ?string $signatureFilePath): string
    {
        if ($signatureFilePath && Storage::disk('local')->exists($signatureFilePath)) {
            return SignatureStorage::toImgTagForPdf(Storage::disk('local')->path($signatureFilePath));
        }

        return SignatureStorage::toImgTag($signatureDataUri);
    }

    protected function resolveStampImgTag(
        Application $application,
        ?string $signatureDataUri,
        ?string $signatureFilePath,
    ): string {
        if (! $this->hasSignature($signatureDataUri, $signatureFilePath)) {
            return '';
        }

        return LetterFieldSchema::stampImgTag($application);
    }

    protected function resolveTtdSignBlock(
        Application $application,
        ?string $signatureDataUri,
        ?string $signatureFilePath,
    ): string {
        $signature = $this->resolveSignatureImgTag($signatureDataUri, $signatureFilePath);
        $stamp = $this->resolveStampImgTag($application, $signatureDataUri, $signatureFilePath);
        $withCap = $stamp !== '' ? ' ttd-sign-stack--with-cap' : '';
        $blockClass = 'ttd-sign-block'.($stamp !== '' ? ' ttd-sign-block--with-cap' : '');

        return '<div class="'.$blockClass.'"><div class="ttd-sign-stack'.$withCap.'">'
            .'<div class="ttd-img">'.$signature.'</div>'
            .($stamp !== '' ? '<div class="ttd-cap">'.$stamp.'</div>' : '')
            .'</div></div>';
    }

    protected function hasSignature(?string $signatureDataUri, ?string $signatureFilePath): bool
    {
        if ($signatureFilePath && Storage::disk('local')->exists($signatureFilePath)) {
            return true;
        }

        return $signatureDataUri && ! SignatureStorage::isBlank($signatureDataUri);
    }

    public static function suggestLetterNumber(Application $application): string
    {
        $application->loadMissing(['resident.household.rtProfile', 'assignedRtProfile', 'generatedLetter']);

        if ($application->generatedLetter?->letter_number) {
            return $application->generatedLetter->letter_number;
        }

        $rt = $application->resolvedRtProfile()
            ?? $application->resident?->household?->rtProfile;
        $rtNumber = RtProfile::normalizeRtNumber($rt?->rt_number);

        return 'RT'.$rtNumber.'/'.now()->format('m/Y').'/'.str_pad((string) $application->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, string>  $fields
     */
    protected function resolveLetterNumber(Application $application, array $fields = []): string
    {
        $fromFields = trim((string) ($fields['nomor_surat'] ?? ''));
        if ($fromFields !== '') {
            return $fromFields;
        }

        return self::suggestLetterNumber($application);
    }

    /**
     * @param  array<string, string>  $vars
     */
    protected function renderTemplate(string $body, array $vars): string
    {
        $content = $body;
        $htmlKeys = ['ttd_gambar', 'cap_rt_gambar', 'ttd_tanda_cap', 'logo_rt', 'logo_kop'];

        foreach ($vars as $key => $value) {
            $placeholder = '{{'.$key.'}}';
            if (in_array($key, $htmlKeys, true)) {
                $content = str_replace($placeholder, (string) $value, $content);
            } else {
                $text = trim((string) $value);
                if ($text === '') {
                    $text = '—';
                }
                $content = str_replace($placeholder, e($text), $content);
            }
        }

        return '<!DOCTYPE html><html><head><meta charset="utf-8"><style>'
            .LetterPdfStyles::css()
            .'</style></head><body>'.$content.'</body></html>';
    }
}
