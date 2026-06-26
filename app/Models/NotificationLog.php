<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'application_id',
        'resident_id',
        'citizen_report_id',
        'rt_publication_id',
        'phone',
        'event',
        'message',
        'status',
        'whatsapp_message_id',
        'error_message',
        'sent_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function citizenReport(): BelongsTo
    {
        return $this->belongsTo(CitizenReport::class);
    }

    public function rtPublication(): BelongsTo
    {
        return $this->belongsTo(RtPublication::class);
    }

    public function eventLabel(): string
    {
        return match ($this->event) {
            'pendataan_submitted' => 'Pengajuan diterima',
            'pendataan_verified' => 'Data disetujui',
            'pendataan_registered_by_rt' => 'Data dicatat RT',
            'pendataan_incomplete' => 'Permintaan lengkapi berkas',
            'pendataan_rejected' => 'Pengajuan ditolak',
            'submitted' => 'Permohonan diajukan',
            'verified' => 'Permohonan diterima RT',
            'incomplete' => 'Permohonan perlu dilengkapi (arsip)',
            'approved' => 'Surat siap / selesai',
            'rejected' => 'Permohonan ditolak',
            'application_rejected' => 'Permohonan ditolak',
            'letter_sent' => 'PDF surat dikirim',
            'letter_ready' => 'Surat selesai',
            'report_submitted' => 'Laporan diterima',
            'report_status_updated' => 'Status laporan diperbarui',
            'publication_broadcast' => 'Pengumuman dikirim',
            default => $this->event,
        };
    }

    /** @param  Builder<NotificationLog>  $query */
    public function scopeForRtProfile(Builder $query, RtProfile $rt): Builder
    {
        $profileIds = RtProfile::profileIdsForRtNumber($rt->rt_number);

        return $query->where(function (Builder $sub) use ($profileIds) {
            $sub->whereHas('application', fn (Builder $q) => $q->whereIn('rt_profile_id', $profileIds))
                ->orWhereHas('resident.household', fn (Builder $q) => $q->whereIn('rt_profile_id', $profileIds))
                ->orWhereHas('citizenReport', fn (Builder $q) => $q->whereIn('rt_profile_id', $profileIds))
                ->orWhereHas('rtPublication', fn (Builder $q) => $q->whereIn('rt_profile_id', $profileIds));
        });
    }

    /** @param  Builder<NotificationLog>  $query */
    public function scopeForResidentPendataan(Builder $query, int $residentId): Builder
    {
        return $query->where('resident_id', $residentId)
            ->where('event', 'like', 'pendataan_%');
    }

    /** @param  Builder<NotificationLog>  $query */
    public function scopeForCitizenReport(Builder $query, int $reportId): Builder
    {
        return $query->where('citizen_report_id', $reportId);
    }

    /** @param  Builder<NotificationLog>  $query */
    public function scopeForPublication(Builder $query, int $publicationId): Builder
    {
        return $query->where('rt_publication_id', $publicationId)
            ->where('event', 'publication_broadcast');
    }
}
