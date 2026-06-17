<?php

namespace App\Enums;

enum PermanentDeletionRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu admin',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'lw-badge--amber',
            self::Approved => 'lw-badge--green',
            self::Rejected => 'lw-badge--red',
        };
    }
}
