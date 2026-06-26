<?php

namespace App\Services;

use App\Models\Application;
use App\Models\GeneratedLetter;
use App\Models\LetterTemplate;
use App\Support\LetterQrCode;
use App\Support\LetterVerificationLink;
use App\Support\LetterFieldSchema;
use App\Support\LetterKopFields;
use App\Support\LetterPdfStyles;
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
        ?int $signedBy = null,
    ): GeneratedLetter {
        $application->loadMissing(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType', 'generatedLetter']);

        $template = LetterTemplate::where('service_type_id', $application->service_type_id)
            ->where('is_active', true)
            ->firstOrFail();

        $existingLetter = $application->generatedLetter;
        $verificationToken = LetterVerificationLink::resolveToken($application);

        $merged = $this->mergeFields($application, $fields, $verificationToken);
        $html = $this->renderTemplate($template->body_html, $merged);

        $filename = 'letters/'.Str::slug($application->application_number).'-'.now()->format('YmdHis').'.pdf';
        $pdf = Pdf::loadHTML($html)->setPaper('A4');
        Storage::disk('local')->put($filename, $pdf->output());

        $snapshot = collect($merged)
            ->except(['ttd_qrcode', 'nomor_surat', 'tanggal'])
            ->all();

        $publishCount = ($existingLetter?->publish_count ?? 0) + 1;

        return GeneratedLetter::updateOrCreate(
            ['application_id' => $application->id],
            [
                'letter_template_id' => $template->id,
                'file_path' => $filename,
                'letter_number' => $merged['nomor_surat'],
                'verification_token' => $verificationToken,
                'letter_fields' => $snapshot,
                'signature_path' => null,
                'signed_at' => now(),
                'signed_by' => $signedBy ?? $existingLetter?->signed_by,
                'issued_at' => now(),
                'publish_count' => $publishCount,
            ]
        );
    }
    /**
     * @param  array<string, string>  $fields
     */
    public function previewHtml(Application $application, array $fields = []): string
    {
        $application->loadMissing(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType', 'generatedLetter']);

        $template = LetterTemplate::where('service_type_id', $application->service_type_id)
            ->where('is_active', true)
            ->firstOrFail();

        $verificationToken = LetterVerificationLink::resolveToken($application);
        $merged = $this->mergeFields($application, $fields, $verificationToken);

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
    protected function mergeFields(Application $application, array $fields, string $verificationToken): array
    {
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
        $merged['ttd_qrcode'] = LetterQrCode::block($application, $verificationToken);
        $merged['logo_rt'] = LetterFieldSchema::logoImgTag($application);
        if (! filled($merged['logo_kop'] ?? null)) {
            $merged['logo_kop'] = LetterKopFields::kopLogoImgTag();
        }

        return $merged;
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
        $htmlKeys = ['ttd_qrcode', 'logo_rt', 'logo_kop'];

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
