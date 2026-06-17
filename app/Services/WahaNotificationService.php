<?php

namespace App\Services;

use App\Enums\RtPublicationType;
use App\Models\Application;
use App\Models\CitizenReport;
use App\Models\NotificationLog;
use App\Models\Resident;
use App\Models\RtProfile;
use App\Models\RtPublication;
use App\Support\LetterDownloadLink;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WahaNotificationService
{
    /**
     * @param  array{application_id?: int, citizen_report_id?: int, rt_publication_id?: int}  $context
     */
    public function sendToResident(
        Resident $resident,
        string $event,
        string $message,
        ?Application $application = null,
        array $context = [],
    ): NotificationLog {
        if ($application) {
            $context['application_id'] = $application->id;
        }

        $phone = $resident->whatsappNotificationPhone();

        $log = $this->createLog(
            phone: $phone ?? '',
            event: $event,
            message: $message,
            residentId: $resident->id,
            context: $context,
        );

        if (! $phone) {
            $log->update(['status' => 'skipped', 'error_message' => 'Notifikasi WA nonaktif atau nomor kosong']);

            return $log;
        }

        return $this->deliverText($log, $phone);
    }

    /**
     * @param  array{resident_id?: int, citizen_report_id?: int, rt_publication_id?: int, application_id?: int}  $context
     */
    public function sendToPhone(
        string $phone,
        string $event,
        string $message,
        array $context = [],
    ): NotificationLog {
        $log = $this->createLog(
            phone: $phone,
            event: $event,
            message: $message,
            residentId: $context['resident_id'] ?? null,
            context: $context,
        );

        if (! filled($phone)) {
            $log->update(['status' => 'skipped', 'error_message' => 'Nomor kosong']);

            return $log;
        }

        return $this->deliverText($log, $phone);
    }

    public function notifyApplicationStatus(Application $application): ?NotificationLog
    {
        $application->loadMissing(['resident.household.rtProfile', 'serviceType', 'assignedRtProfile']);

        $event = $application->status->notifyEvent();

        if (! $event || ! $application->resident) {
            return null;
        }

        $message = $this->buildMessage($application, $event);

        return $this->sendToResident($application->resident, $event, $message, $application);
    }

    public function notifyApplicationRejected(Application $application, string $notes): ?NotificationLog
    {
        $application->loadMissing(['resident.household.rtProfile', 'serviceType', 'assignedRtProfile']);

        if (! $application->resident) {
            return null;
        }

        $application->rejection_reason = $notes;
        $message = $this->buildMessage($application, 'rejected');

        return $this->sendToResident($application->resident, 'application_rejected', $message, $application);
    }

    public function notifyLetterReady(Application $application, string $letterNumber): NotificationLog
    {
        $application->loadMissing(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType']);

        $resident = $application->resident;
        $message = $this->buildLetterReadyMessage($application, $letterNumber);

        if (! $resident) {
            $log = $this->createLog(
                phone: '',
                event: 'letter_ready',
                message: $message,
                residentId: null,
                context: ['application_id' => $application->id],
            );
            $log->update(['status' => 'skipped', 'error_message' => 'Data pemohon tidak tersedia']);

            return $log;
        }

        return $this->sendToResident($resident, 'letter_ready', $message, $application);
    }

    public function sendLetterPdf(Application $application): NotificationLog
    {
        $application->loadMissing(['resident.household.rtProfile', 'assignedRtProfile', 'serviceType', 'generatedLetter']);

        $resident = $application->resident;
        $letter = $application->generatedLetter;
        $caption = $this->buildLetterCaption($application);

        $phone = $resident->whatsappNotificationPhone();

        $log = $this->createLog(
            phone: $phone ?? '',
            event: 'letter_sent',
            message: $caption,
            residentId: $resident->id,
            context: ['application_id' => $application->id],
        );

        if (! $letter || ! Storage::disk('local')->exists($letter->file_path)) {
            $log->update(['status' => 'failed', 'error_message' => 'PDF surat belum tersedia']);

            return $log;
        }

        if (! $letter->signature_path && ! $letter->signed_at) {
            $log->update(['status' => 'failed', 'error_message' => 'Surat belum ditandatangani Ketua RT']);

            return $log;
        }

        if (! $phone) {
            $log->update(['status' => 'skipped', 'error_message' => 'Notifikasi WA nonaktif atau nomor kosong']);

            return $log;
        }

        $chatId = PhoneNormalizer::toWhatsAppChatId($phone);

        if (! $chatId) {
            $log->update(['status' => 'failed', 'error_message' => 'Format nomor tidak valid']);

            return $log;
        }

        if ($sessionError = $this->ensureSessionWorking()) {
            $log->update(['status' => 'failed', 'error_message' => $sessionError]);

            return $log;
        }

        try {
            $filename = 'Surat-'.$application->application_number.'.pdf';
            $pdfContent = Storage::disk('local')->get($letter->file_path);

            $fileResponse = $this->postWaha('/api/sendFile', [
                'session' => config('waha.session'),
                'chatId' => $chatId,
                'file' => [
                    'mimetype' => 'application/pdf',
                    'filename' => $filename,
                    'data' => base64_encode($pdfContent),
                ],
                'caption' => $caption,
            ]);

            if ($fileResponse->successful()) {
                return $this->finalizeLog($log, $fileResponse);
            }

            $downloadUrl = LetterDownloadLink::signedUrl($application);
            $fallbackText = $downloadUrl
                ? $caption."\n\nUnduh surat PDF:\n".$downloadUrl
                : $caption;

            $textResponse = $this->postWaha('/api/sendText', [
                'session' => config('waha.session'),
                'chatId' => $chatId,
                'text' => $fallbackText,
            ]);

            return $this->finalizeLog($log, $textResponse);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error('WAHA letter send exception', ['error' => $e->getMessage()]);

            return $log;
        }
    }

    public function notifyPendataanSubmitted(Resident $head, RtProfile $rt): NotificationLog
    {
        return $this->sendPendataanMessage($head, $rt, 'submitted', 'pendataan_submitted');
    }

    public function notifyPendataanVerified(Resident $head, RtProfile $rt): NotificationLog
    {
        return $this->sendPendataanMessage($head, $rt, 'verified', 'pendataan_verified');
    }

    public function notifyPendataanIncomplete(Resident $head, RtProfile $rt, string $notes): NotificationLog
    {
        $message = $this->formatPendataanTemplate('incomplete', $head, $rt, [
            'catatan' => $notes,
        ]);

        return $this->sendToResident($head, 'pendataan_incomplete', $message);
    }

    public function notifyPendataanRejected(Resident $head, RtProfile $rt, string $notes): NotificationLog
    {
        $message = $this->formatPendataanTemplate('rejected', $head, $rt, [
            'catatan' => $notes,
        ]);

        return $this->sendToResident($head, 'pendataan_rejected', $message);
    }

    public function notifyReportSubmitted(CitizenReport $report): NotificationLog
    {
        $report->loadMissing('rtProfile');
        $message = $this->formatReportTemplate('submitted', $report);

        return $this->sendToPhone($report->phone, 'report_submitted', $message, [
            'citizen_report_id' => $report->id,
        ]);
    }

    public function notifyReportStatusUpdated(CitizenReport $report): NotificationLog
    {
        $report->loadMissing('rtProfile');
        $message = $this->formatReportTemplate('status_updated', $report, [
            'status' => $report->status->label(),
            'catatan' => filled($report->response_note) ? "Catatan: {$report->response_note}" : '',
        ]);

        return $this->sendToPhone($report->phone, 'report_status_updated', $message, [
            'citizen_report_id' => $report->id,
        ]);
    }

    public function notifyPublication(Resident $resident, RtPublication $publication): NotificationLog
    {
        $publication->loadMissing('rtProfile');
        $rt = $publication->rtProfile;
        $message = $this->formatPublicationTemplate($publication, $resident);

        return $this->sendToResident($resident, 'publication_broadcast', $message, null, [
            'rt_publication_id' => $publication->id,
        ]);
    }

    protected function sendPendataanMessage(Resident $head, RtProfile $rt, string $templateKey, string $event): NotificationLog
    {
        $message = $this->formatPendataanTemplate($templateKey, $head, $rt);

        return $this->sendToResident($head, $event, $message);
    }

    /** @param  array<string, string>  $extra */
    protected function formatPendataanTemplate(string $key, Resident $head, RtProfile $rt, array $extra = []): string
    {
        $template = config("kelurahan.wa_pendataan.{$key}", '');
        $replacements = array_merge([
            'nama' => $head->name,
            'rt' => $rt->displayName(),
            'url' => rtrim(config('app.url'), '/'),
            'layanan_url' => rtrim(config('app.url'), '/').($head->household?->pendataanServicePath() ?? '/layanan'),
            'portal' => config('kelurahan.portal_nama'),
        ], $extra);

        return $this->replaceTemplate($template, $replacements);
    }

    /** @param  array<string, string>  $extra */
    protected function formatReportTemplate(string $key, CitizenReport $report, array $extra = []): string
    {
        $template = config("kelurahan.wa_laporan.{$key}", '');
        $replacements = array_merge([
            'nama' => $report->reporter_name,
            'no' => $report->report_number,
            'perihal' => $report->subject,
            'rt' => $report->rtProfile?->displayName() ?? 'RT',
            'url' => rtrim(config('app.url'), '/'),
            'portal' => config('kelurahan.portal_nama'),
            'status' => $report->status->label(),
            'catatan' => '',
        ], $extra);

        return $this->replaceTemplate($template, $replacements);
    }

    protected function formatPublicationTemplate(RtPublication $publication, Resident $resident): string
    {
        $typeKey = $publication->type === RtPublicationType::Kegiatan ? 'kegiatan' : 'pengumuman';
        $template = config("kelurahan.wa_publikasi.{$typeKey}", '');
        $rt = $publication->rtProfile?->displayName() ?? 'RT';
        $tanggal = $publication->tanggal?->locale('id')->translatedFormat('d F Y') ?? '—';
        $ringkasan = trim((string) $publication->ringkasan);
        if ($ringkasan === '') {
            $ringkasan = '—';
        }

        return $this->replaceTemplate($template, [
            'nama' => $resident->name,
            'rt' => $rt,
            'judul' => $publication->judul,
            'tanggal' => $tanggal,
            'lokasi' => $publication->lokasi ?: '—',
            'ringkasan' => $ringkasan,
            'url' => rtrim(config('app.url'), '/'),
            'portal' => config('kelurahan.portal_nama'),
        ]);
    }

    /**
     * @param  array{application_id?: int, citizen_report_id?: int, rt_publication_id?: int}  $context
     */
    protected function createLog(
        string $phone,
        string $event,
        string $message,
        ?int $residentId = null,
        array $context = [],
    ): NotificationLog {
        return NotificationLog::create([
            'application_id' => $context['application_id'] ?? null,
            'resident_id' => $residentId,
            'citizen_report_id' => $context['citizen_report_id'] ?? null,
            'rt_publication_id' => $context['rt_publication_id'] ?? null,
            'phone' => $phone,
            'event' => $event,
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    protected function deliverText(NotificationLog $log, string $phone): NotificationLog
    {
        $chatId = PhoneNormalizer::toWhatsAppChatId($phone);

        if (! $chatId) {
            $log->update(['status' => 'failed', 'error_message' => 'Format nomor tidak valid']);

            return $log;
        }

        if ($sessionError = $this->ensureSessionWorking()) {
            $log->update(['status' => 'failed', 'error_message' => $sessionError]);

            return $log;
        }

        try {
            $response = $this->postWaha('/api/sendText', [
                'session' => config('waha.session'),
                'chatId' => $chatId,
                'text' => $log->message,
            ]);

            return $this->finalizeLog($log, $response);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error('WAHA exception', ['error' => $e->getMessage()]);

            return $log;
        }
    }

    protected function buildLetterReadyMessage(Application $application, string $letterNumber): string
    {
        $resident = $application->resident;
        $rt = $resident?->household?->rtProfile?->displayName()
            ?? $application->assignedRtProfile?->displayName()
            ?? 'RT';

        $template = config('kelurahan.wa_letter', '');

        if ($template) {
            return $this->replaceTemplate($template, [
                'nama' => $application->applicantName(),
                'nik' => $application->applicantNik() ?? '—',
                'layanan' => $application->serviceType->name,
                'no' => $application->application_number,
                'nomor_surat' => $letterNumber,
                'rt' => $rt,
                'url' => rtrim(config('app.url'), '/'),
                'portal' => config('kelurahan.portal_nama'),
            ]);
        }

        $name = $application->applicantName();

        return "Yth. {$name},\n\nSurat pengantar *{$application->serviceType->name}* telah diterbitkan {$rt}.\nNomor surat: {$letterNumber}\n\nSilakan ambil surat fisik di sekretariat {$rt}.\n\n— ".config('kelurahan.portal_nama');
    }

    protected function buildLetterCaption(Application $application): string
    {
        $resident = $application->resident;
        $rt = $resident->household?->rtProfile?->displayName()
            ?? $application->assignedRtProfile?->displayName()
            ?? 'RT';

        $template = config('kelurahan.wa_letter', '');

        if ($template) {
            return $this->replaceTemplate($template, [
                'nama' => $resident->name,
                'layanan' => $application->serviceType->name,
                'no' => $application->application_number,
                'nomor_surat' => $application->generatedLetter?->letter_number ?? $application->application_number,
                'rt' => $rt,
                'url' => rtrim(config('app.url'), '/'),
                'portal' => config('kelurahan.portal_nama'),
            ]);
        }

        return "Yth. {$resident->name},\n\nSurat pengantar *{$application->serviceType->name}* ({$application->application_number}) dari {$rt} terlampir.\n\n— ".config('kelurahan.portal_nama');
    }

    protected function buildMessage(Application $application, string $event): string
    {
        $name = $application->applicantName();
        $rt = $application->applicantRtLabel();
        if ($rt === '—') {
            $rt = $application->assignedRtProfile?->displayName() ?? 'RT';
        }
        $catatan = filled($application->rejection_reason)
            ? "Alasan/catatan: {$application->rejection_reason}"
            : '';

        $replacements = [
            'nama' => $name,
            'layanan' => $application->serviceType->name,
            'no' => $application->application_number,
            'rt' => $rt,
            'url' => rtrim(config('app.url'), '/'),
            'portal' => config('kelurahan.portal_nama'),
            'catatan' => $application->rejection_reason ?? '',
        ];

        $template = config("kelurahan.wa_permohonan.{$event}");

        if ($template) {
            $message = $this->replaceTemplate($template, $replacements);
            if ($event === 'rejected' && $catatan) {
                return $message;
            }

            return $message;
        }

        return match ($event) {
            'submitted' => "Yth. {$name},\n\nPermohonan *{$replacements['layanan']}* ({$replacements['no']}) di {$rt} telah *diterima* dan menunggu verifikasi.\n\n— {$replacements['portal']}",
            'verified' => "Yth. {$name},\n\nPermohonan *{$replacements['layanan']}* ({$replacements['no']}) telah *diverifikasi* RT.\n\n— {$replacements['portal']}",
            'incomplete' => "Yth. {$name},\n\nPermohonan *{$replacements['layanan']}* ({$replacements['no']}) perlu dilengkapi:\n{$replacements['catatan']}\n\n— {$replacements['portal']}",
            'approved' => "Yth. {$name},\n\nPermohonan *{$replacements['layanan']}* ({$replacements['no']}) telah *disetujui*.\n\n— {$replacements['portal']}",
            'rejected' => "Yth. {$name},\n\nPermohonan *{$replacements['layanan']}* ({$replacements['no']}) *ditolak*.".($catatan ? "\n{$catatan}" : '')."\n\n— {$replacements['portal']}",
            default => "Update permohonan {$replacements['no']}: {$application->status->label()}",
        };
    }

    protected function ensureSessionWorking(): ?string
    {
        if (! config('waha.api_key')) {
            return 'WAHA API key belum dikonfigurasi';
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders(['X-Api-Key' => config('waha.api_key')])
                ->get(rtrim(config('waha.base_url'), '/').'/api/sessions/'.config('waha.session'));

            if (! $response->successful()) {
                return 'Tidak dapat menghubungi server WhatsApp';
            }

            $status = $response->json('status');

            if ($status === 'WORKING') {
                return null;
            }

            return match ($status) {
                'SCAN_QR_CODE' => 'Sesi WhatsApp menunggu scan QR. Hubungi admin portal.',
                'FAILED', 'STOPPED' => 'Sesi WhatsApp belum terhubung. Hubungi admin untuk menghubungkan kembali.',
                default => 'Sesi WhatsApp belum siap (status: '.($status ?? 'tidak diketahui').')',
            };
        } catch (\Throwable $e) {
            return 'Tidak dapat menghubungi server WhatsApp: '.$e->getMessage();
        }
    }

    /** @param  array<string, mixed>  $payload */
    protected function postWaha(string $endpoint, array $payload): Response
    {
        return Http::timeout(30)
            ->withHeaders(['X-Api-Key' => config('waha.api_key')])
            ->post(rtrim(config('waha.base_url'), '/').$endpoint, $payload);
    }

    protected function finalizeLog(NotificationLog $log, Response $response): NotificationLog
    {
        if ($response->successful()) {
            $log->update([
                'status' => 'sent',
                'whatsapp_message_id' => $response->json('id') ?? $response->json('messageId'),
                'sent_at' => now(),
            ]);
        } else {
            $log->update([
                'status' => 'failed',
                'error_message' => $response->body(),
            ]);
            Log::warning('WAHA send failed', ['body' => $response->body(), 'status' => $response->status()]);
        }

        return $log;
    }

    /** @param  array<string, string>  $replacements */
    protected function replaceTemplate(string $template, array $replacements): string
    {
        return str_replace(
            array_map(fn ($k) => '{'.$k.'}', array_keys($replacements)),
            array_values($replacements),
            $template
        );
    }
}
