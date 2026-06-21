<?php

namespace App\Support;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Carbon\Carbon;

class ApplicationTimeline
{
    /** @return list<array{key: string, title: string, desc?: string, state: string, date?: string, datetime?: string}> */
    public static function stepsFor(Application $application): array
    {
        $status = $application->status;
        $submitted = $application->submitted_at;
        $completed = $application->completed_at;

        $definitions = [
            [
                'key' => 'diajukan',
                'title' => 'Permohonan diajukan',
                'desc' => 'Data permohonan diterima sistem.',
                'reached' => true,
                'date' => $submitted,
            ],
            [
                'key' => 'verifikasi',
                'title' => 'Verifikasi pengurus RT',
                'desc' => 'Pengurus memeriksa berkas dan menerima permohonan.',
                'reached' => self::hasReached($status, ApplicationStatus::VerifikasiRt),
                'date' => self::hasReached($status, ApplicationStatus::VerifikasiRt) ? ($application->updated_at ?? $submitted) : null,
            ],
            [
                'key' => 'siap',
                'title' => 'Surat siap',
                'desc' => 'Surat PDF diterbitkan. Ambil di sekretariat RT atau cek WhatsApp jika pengurus mengirim PDF.',
                'reached' => $status === ApplicationStatus::SiapDiambil,
                'date' => $status === ApplicationStatus::SiapDiambil ? $completed : null,
            ],
        ];

        if ($status === ApplicationStatus::Ditolak) {
            return [
                [
                    'key' => 'diajukan',
                    'title' => 'Permohonan diajukan',
                    'state' => 'done',
                    'date' => self::formatDate($submitted),
                    'datetime' => $submitted?->toIso8601String(),
                ],
                [
                    'key' => 'ditolak',
                    'title' => 'Permohonan ditolak',
                    'desc' => $application->rejection_reason ?: 'Hubungi pengurus RT untuk informasi lebih lanjut.',
                    'state' => 'active',
                    'date' => self::formatDate($completed),
                    'datetime' => $completed?->toIso8601String(),
                ],
            ];
        }

        $steps = [];
        $currentKey = match ($status) {
            ApplicationStatus::Diajukan => 'diajukan',
            ApplicationStatus::VerifikasiRt => 'verifikasi',
            ApplicationStatus::SiapDiambil, ApplicationStatus::Disetujui => 'siap',
            default => 'diajukan',
        };

        foreach ($definitions as $def) {
            $state = 'pending';
            if ($def['reached']) {
                $state = ($def['key'] === $currentKey) ? 'active' : 'done';
            } elseif ($def['key'] === $currentKey) {
                $state = 'active';
            }

            $steps[] = [
                'key' => $def['key'],
                'title' => $def['title'],
                'desc' => $def['desc'] ?? null,
                'state' => $state,
                'date' => self::formatDate($def['date'] ?? null),
                'datetime' => ($def['date'] ?? null)?->toIso8601String(),
            ];
        }

        return $steps;
    }

    private static function hasReached(ApplicationStatus $current, ApplicationStatus $target): bool
    {
        $order = [
            ApplicationStatus::Diajukan->value => 1,
            ApplicationStatus::VerifikasiRt->value => 2,
            ApplicationStatus::Disetujui->value => 3,
            ApplicationStatus::SiapDiambil->value => 3,
            ApplicationStatus::Ditolak->value => 0,
        ];

        return ($order[$current->value] ?? 0) >= ($order[$target->value] ?? 0);
    }

    private static function formatDate(?Carbon $date): ?string
    {
        return $date?->locale('id')->translatedFormat('d F Y, H:i').' WIT';
    }
}
