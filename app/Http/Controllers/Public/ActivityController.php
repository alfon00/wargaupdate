<?php

namespace App\Http\Controllers\Public;

use App\Enums\RtPublicationType;
use App\Http\Controllers\Controller;
use App\Models\RtPublication;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(): View
    {
        return view('public.activities.index', [
            'kegiatan' => self::sortedKegiatan(),
            'pengumuman' => self::sortedPengumuman(),
        ]);
    }

    /** @return list<array{id: int, date: string, title: string, rt: string, lokasi: string|null}> */
    public static function calendarEventsFrom($publications): array
    {
        return $publications
            ->filter(fn (RtPublication $pub) => $pub->tanggal !== null)
            ->map(fn (RtPublication $pub) => [
                'id' => $pub->id,
                'date' => $pub->tanggal->format('Y-m-d'),
                'title' => $pub->judul,
                'rt' => $pub->rtProfile?->displayName() ?? 'RT',
                'lokasi' => $pub->lokasi,
            ])
            ->values()
            ->all();
    }

    /** @return list<array{url: string, title: string, date: string}> */
    public static function galleryItemsFrom($publications): array
    {
        return $publications
            ->filter(fn (RtPublication $pub) => filled($pub->foto_path))
            ->map(fn (RtPublication $pub) => [
                'url' => $pub->fotoUrl() ?? asset('images/kegiatan/placeholder.svg'),
                'title' => $pub->judul,
                'date' => self::formatTanggal($pub->tanggal?->format('Y-m-d')),
            ])
            ->values()
            ->all();
    }

    /** @return array{status: string, status_label: string, minggu_ini: bool} */
    public static function resolveStatus(?Carbon $tanggal): array
    {
        if ($tanggal === null) {
            return [
                'status' => '',
                'status_label' => '',
                'minggu_ini' => false,
            ];
        }

        $today = Carbon::today('Asia/Jayapura');
        $date = $tanggal->copy()->timezone('Asia/Jayapura')->startOfDay();

        $mingguIni = $date->betweenIncluded(
            $today->copy()->startOfWeek(),
            $today->copy()->endOfWeek()
        );

        if ($date->isSameDay($today)) {
            return [
                'status' => 'hari_ini',
                'status_label' => 'Hari Ini',
                'minggu_ini' => $mingguIni,
            ];
        }

        if ($date->isFuture()) {
            return [
                'status' => 'akan_datang',
                'status_label' => 'Akan Datang',
                'minggu_ini' => $mingguIni,
            ];
        }

        return [
            'status' => 'selesai',
            'status_label' => 'Selesai',
            'minggu_ini' => $mingguIni,
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public static function sortedKegiatan(): Collection
    {
        return self::queryPublications(RtPublicationType::Kegiatan);
    }

    /** @return Collection<int, array<string, mixed>> */
    public static function sortedPengumuman(): Collection
    {
        return self::queryPublications(RtPublicationType::Pengumuman);
    }

    /** @return Collection<int, array<string, mixed>> */
    private static function queryPublications(RtPublicationType $type): Collection
    {
        $query = RtPublication::query()
            ->forPublic()
            ->published()
            ->with('rtProfile')
            ->where('type', $type);

        if ($type === RtPublicationType::Kegiatan) {
            $query->orderByDesc('tanggal')->orderByDesc('published_at');
        } else {
            $query->visibleOnPublic()
                ->orderByDesc('published_at')
                ->orderByDesc('tanggal');
        }

        return $query->get()->map(fn (RtPublication $pub) => self::mapPublication($pub, $type));
    }

    /** @return array<string, mixed> */
    private static function mapPublication(RtPublication $pub, RtPublicationType $type): array
    {
        $tanggal = $pub->tanggal?->format('Y-m-d') ?? '';
        $rtLabel = $pub->rtProfile?->displayName() ?? 'RT';
        $status = self::resolveStatus($pub->tanggal);

        $waktuLabel = null;
        if ($pub->published_at) {
            $waktuLabel = $pub->published_at
                ->timezone('Asia/Jayapura')
                ->format('H:i').' WIT';
        }

        $searchText = Str::lower(implode(' ', array_filter([
            $pub->judul,
            $pub->ringkasan,
            $pub->lokasi,
            $rtLabel,
            $status['status_label'],
        ])));

        $berlakuHingga = $type === RtPublicationType::Pengumuman
            ? $pub->effectiveExpiresAt()
            : null;

        return [
            'judul' => $pub->judul,
            'ringkasan' => $pub->ringkasan,
            'tanggal' => $tanggal,
            'tanggal_label' => self::formatTanggal($tanggal ?: null),
            'lokasi' => $pub->lokasi,
            'foto_url' => $type === RtPublicationType::Kegiatan
                ? ($pub->fotoUrl() ?? asset('images/kegiatan/placeholder.svg'))
                : null,
            'rt_label' => $rtLabel,
            'status' => $status['status'],
            'status_label' => $status['status_label'],
            'minggu_ini' => $status['minggu_ini'],
            'waktu_label' => $waktuLabel,
            'berlaku_hingga' => $berlakuHingga?->format('Y-m-d') ?? '',
            'berlaku_hingga_label' => $berlakuHingga
                ? $berlakuHingga->locale('id')->translatedFormat('d M Y')
                : '',
            'search_text' => $searchText,
        ];
    }

    public static function resolveFotoUrl(?string $foto): string
    {
        $placeholder = asset('images/kegiatan/placeholder.svg');

        if (! filled($foto)) {
            return $placeholder;
        }

        if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) {
            return $foto;
        }

        $path = ltrim($foto, '/');

        if (is_file(public_path($path))) {
            return asset($path);
        }

        return $placeholder;
    }

    public static function formatTanggal(?string $tanggal): string
    {
        if (! filled($tanggal)) {
            return '';
        }

        try {
            return Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y');
        } catch (\Throwable) {
            return $tanggal;
        }
    }
}
