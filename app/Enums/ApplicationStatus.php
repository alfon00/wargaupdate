<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Diajukan = 'diajukan';
    case VerifikasiRt = 'verifikasi_rt';
    case PerluLengkap = 'perlu_lengkap';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case SiapDiambil = 'siap_diambil';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Diajukan => 'Diajukan',
            self::VerifikasiRt => 'Verifikasi RT',
            self::PerluLengkap => 'Perlu lengkapi berkas',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
            self::SiapDiambil => 'Siap Diambil',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Diajukan => 'lw-badge--blue',
            self::VerifikasiRt => 'lw-badge--muted',
            self::PerluLengkap => 'lw-badge--amber',
            self::Disetujui, self::SiapDiambil => 'lw-badge--green',
            self::Ditolak => 'lw-badge--red',
            default => '',
        };
    }

    public function notifyEvent(): ?string
    {
        return match ($this) {
            self::Diajukan => 'submitted',
            self::VerifikasiRt => 'verified',
            self::PerluLengkap => 'incomplete',
            self::Disetujui, self::SiapDiambil => 'approved',
            self::Ditolak => 'rejected',
            default => null,
        };
    }

    public function canBeReviewedByRt(): bool
    {
        return in_array($this, [self::Diajukan, self::PerluLengkap], true);
    }

    public function canAcceptByRt(): bool
    {
        return $this->canBeReviewedByRt();
    }

    public function canRejectByRt(): bool
    {
        return in_array($this, [
            self::Diajukan,
            self::PerluLengkap,
            self::VerifikasiRt,
            self::Disetujui,
        ], true);
    }

    public function canRequestCompletionByRt(): bool
    {
        return $this->canRejectByRt();
    }

    public function showsReviewActionsSection(): bool
    {
        return $this->canRejectByRt() || $this->canAcceptByRt();
    }

    public function canGenerateLetter(): bool
    {
        return in_array($this, [self::VerifikasiRt, self::Disetujui, self::SiapDiambil], true);
    }

    public function canIssueManualLetter(): bool
    {
        return in_array($this, [self::Diajukan, self::VerifikasiRt], true);
    }

    public function showsManualLetterSection(): bool
    {
        return $this->canIssueManualLetter() || $this === self::SiapDiambil;
    }

    public function canMarkReady(): bool
    {
        return in_array($this, [self::VerifikasiRt, self::Disetujui], true);
    }
}
