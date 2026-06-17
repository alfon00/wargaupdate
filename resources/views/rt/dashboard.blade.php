@extends('layouts.panel')

@section('title', 'Dashboard RT')

@section('content')
<div class="lw-rt-page lw-rt-dashboard-page">
@if($rt)
    @include('rt.partials.page-head', [
        'eyebrow' => $rt->displayName(),
        'title' => 'Dashboard',
        'lead' => 'Ringkasan statistik, prioritas kerja, dan aktivitas terbaru di wilayah RT Anda.',
    ])
@else
    @include('rt.partials.page-head', [
        'title' => 'Dashboard RT',
    ])
@endif

@if(! $rt)
    <div class="lw-panel-alert lw-panel-alert--warn">
        @if($linkingHint ?? null)
            {{ $linkingHint }}
        @else
            Akun belum terhubung ke profil RT. Hubungi admin untuk menetapkan RT.
        @endif
    </div>
@else
    @if($stats['residents_active'] === 0)
        <x-panel.empty-state
            title="Belum ada warga terdaftar"
            description="Dashboard akan menampilkan ringkasan kependudukan setelah Anda mendaftarkan warga aktif di RT."
            :action-url="route('rt.data-warga.create')"
            action-label="+ Daftar KK & warga"
            class="lw-rt-dashboard-onboarding lw-mb-4"
        />
    @endif

    <div class="lw-panel-stats lw-panel-stats--4 lw-rt-dashboard-stats">
        <x-panel.stat-card label="Warga aktif" :value="$stats['residents_active']" />
        <x-panel.stat-card label="Kartu keluarga" :value="$stats['households']" />
        <x-panel.stat-card
            label="Verifikasi pending"
            :value="$stats['pending_pendataan']"
            :variant="$stats['pending_pendataan'] > 0 ? 'highlight' : 'default'"
        />
        <x-panel.stat-card
            label="Permohonan aktif"
            :value="$stats['pending_applications']"
            :variant="$stats['pending_applications'] > 0 ? 'highlight' : 'default'"
        />
    </div>

    <section class="lw-panel-quick lw-rt-dashboard-quick" aria-labelledby="rt-quick-heading">
        <h2 id="rt-quick-heading" class="lw-panel-section-title">Akses cepat</h2>
        <div class="lw-panel-quick-grid">
            <x-panel.quick-card
                :href="route('rt.data-warga.index')"
                title="Data warga lengkap"
                description="Kelola KK dan anggota keluarga di RT Anda"
            />
            <x-panel.quick-card
                :href="route('rt.pendataan.index')"
                title="Verifikasi pendataan"
                description="Tinjau pendaftaran warga baru dari portal"
                :badge="$stats['pending_pendataan'] > 0 ? ($stats['pending_pendataan'].' menunggu') : null"
            />
            <x-panel.quick-card
                :href="route('rt.applications.index')"
                title="Permohonan surat"
                description="Proses permohonan surat pengantar warga"
                :badge="$stats['pending_applications'] > 0 ? ($stats['pending_applications'].' aktif') : null"
            />
        </div>
    </section>

    @if(($priorities ?? collect())->isNotEmpty())
        <section class="lw-rt-dashboard-priorities" aria-labelledby="rt-priorities-heading">
            <h2 id="rt-priorities-heading" class="lw-panel-section-title">Perlu tindakan</h2>
            @include('rt.partials.priority-list', ['priorities' => $priorities])
        </section>
    @endif

    <section class="lw-rt-dash-activity" aria-labelledby="rt-activity-heading">
        <div class="lw-panel-section-head">
            <h2 id="rt-activity-heading" class="lw-panel-section-title">Aktivitas terbaru</h2>
            <a href="{{ route('rt.applications.index') }}" class="lw-panel-link">Semua permohonan →</a>
        </div>
        @if(($activities ?? collect())->isEmpty())
            <x-panel.empty-state
                title="Belum ada aktivitas terbaru"
                description="Permohonan, verifikasi pendataan, dan laporan warga akan tampil di sini."
                :action-url="route('rt.applications.index')"
                action-label="Buka permohonan"
            />
        @else
            <div class="lw-panel-table-wrap">
                <table class="lw-panel-table">
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Aktivitas</th>
                            <th>Waktu</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                            <tr>
                                <td><span class="lw-badge lw-badge--muted">{{ ucfirst($activity['type']) }}</span></td>
                                <td>{{ $activity['title'] }}</td>
                                <td>{{ optional($activity['timestamp'])->timezone('Asia/Jayapura')->format('d/m/Y H:i') ?? '—' }}</td>
                                <td><a href="{{ $activity['url'] }}" class="lw-panel-link">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endif
</div>
@endsection
