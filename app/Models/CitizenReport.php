<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CitizenReport extends Model
{
    protected $fillable = [
        'report_number',
        'rt_profile_id',
        'category',
        'reporter_name',
        'phone',
        'nik',
        'email',
        'application_number',
        'subject',
        'message',
        'incident_location',
        'incident_type',
        'photo_path',
        'status',
        'handled_by',
        'handled_at',
        'response_note',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReportStatus::class,
            'handled_at' => 'datetime',
        ];
    }

    public function rtProfile(): BelongsTo
    {
        return $this->belongsTo(RtProfile::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public static function generateNumber(?string $rtNumber = null): string
    {
        $rt = RtProfile::normalizeRtNumber($rtNumber);
        $prefix = 'LPR'.$rt.'-'.now()->format('Ym');
        $last = static::where('report_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('report_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function getRouteKeyName(): string
    {
        return 'report_number';
    }

    public function categoryLabel(): string
    {
        return config('kelurahan.laporan_kategori.'.$this->category, $this->category);
    }

    /** @param  Builder<CitizenReport>  $query */
    public function scopeForRtProfile(Builder $query, RtProfile $rt): Builder
    {
        $ids = RtProfile::profileIdsForRtNumber($rt->rt_number);

        return $query->whereIn('rt_profile_id', $ids);
    }
}
