<?php

namespace App\Support;

class SuratFaceReadiness
{
    public const STATUS_READY = 'ready';

    public const STATUS_MISSING_DOCUMENTS = 'missing_documents';

    public const STATUS_EXTRACTION_FAILED = 'extraction_failed';

    public function __construct(
        public readonly string $status,
        public readonly string $message,
        public readonly bool $canVerify,
        public readonly string $adminLabel = '',
        public readonly ?string $detail = null,
    ) {}

    public static function ready(): self
    {
        return new self(
            status: self::STATUS_READY,
            message: 'Data wajah siap. Anda dapat melanjutkan verifikasi wajah.',
            canVerify: true,
            adminLabel: 'Siap surat',
        );
    }

    public static function missingDocuments(): self
    {
        return new self(
            status: self::STATUS_MISSING_DOCUMENTS,
            message: 'Berkas KTP/KIA pemohon belum diunggah di sistem RT. Hubungi pengurus RT untuk melengkapi lampiran.',
            canVerify: false,
            adminLabel: 'Perlu KTP/KIA',
        );
    }

    public static function extractionFailed(?string $detail = null): self
    {
        return new self(
            status: self::STATUS_EXTRACTION_FAILED,
            message: 'Wajah pada berkas KTP/KIA tidak dapat dibaca. Minta pengurus RT mengunggah ulang foto KTP/JPG yang jelas (hindari PDF buram atau foto gelap).',
            canVerify: false,
            adminLabel: 'Perlu unggah ulang',
            detail: $detail,
        );
    }

    public function adminBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_READY => 'lw-rt-surat-readiness--ready',
            self::STATUS_MISSING_DOCUMENTS => 'lw-rt-surat-readiness--missing',
            self::STATUS_EXTRACTION_FAILED => 'lw-rt-surat-readiness--failed',
            default => 'lw-rt-surat-readiness--missing',
        };
    }

    public function adminTooltip(): string
    {
        if ($this->detail === null || $this->detail === '') {
            return $this->message;
        }

        return $this->message.' Detail: '.$this->detail;
    }
}
