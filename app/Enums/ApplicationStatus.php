<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Diajukan = 'diajukan';
    case VerifikasiRt = 'verifikasi_rt';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case SiapDiambil = 'siap_diambil';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Diajukan => 'Diajukan',
            self::VerifikasiRt => 'Verifikasi RT',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
            self::SiapDiambil => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Diajukan => 'lw-badge--blue',
            self::VerifikasiRt => 'lw-badge--muted',
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
            self::Disetujui, self::SiapDiambil => 'approved',
            self::Ditolak => 'rejected',
            default => null,
        };
    }

    public function canBeReviewedByRt(): bool
    {
        return $this === self::Diajukan;
    }

    public function needsLetterCompose(): bool
    {
        return $this === self::VerifikasiRt;
    }

    public function rtListActionLabel(): string
    {
        return match ($this) {
            self::Diajukan => 'Verifikasi',
            self::VerifikasiRt => 'Susun surat',
            default => 'Detail',
        };
    }

    public function rtListActionRouteName(): string
    {
        return $this->needsLetterCompose()
            ? 'rt.applications.letter.compose'
            : 'rt.applications.show';
    }

    public function canAcceptByRt(): bool
    {
        return $this->canBeReviewedByRt();
    }

    public function canRejectByRt(): bool
    {
        return in_array($this, [
            self::Diajukan,
            self::VerifikasiRt,
            self::Disetujui,
        ], true);
    }

    public function showsReviewActionsSection(): bool
    {
        return $this->canRejectByRt() || $this->canAcceptByRt();
    }

    public function canGenerateLetter(): bool
    {
        return in_array($this, [self::VerifikasiRt, self::Disetujui, self::SiapDiambil], true);
    }

    public function showsLetterSection(): bool
    {
        return $this->canGenerateLetter();
    }

    public function canMarkReady(): bool
    {
        return in_array($this, [self::VerifikasiRt, self::Disetujui], true);
    }
}
