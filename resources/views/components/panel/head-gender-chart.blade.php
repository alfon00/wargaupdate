@props([
    'chart' => null,
])

<section class="lw-panel-section lw-panel-chart-section" aria-labelledby="head-gender-chart-heading">
    <header class="lw-panel-section-head">
        <h2 id="head-gender-chart-heading" class="lw-panel-section-title lw-panel-section-title--flush">Komposisi kepala keluarga</h2>
    </header>
    <p class="lw-panel-chart-lead">Persentase jenis kelamin kepala keluarga (KK) warga aktif di RT Anda.</p>

    @if(! $chart || ($chart['total'] ?? 0) === 0)
        <x-panel.empty-state
            title="Belum ada data kepala keluarga"
            description="Grafik akan tampil setelah ada kartu keluarga dengan kepala keluarga berstatus domisili aktif."
            :action-url="route('rt.data-warga.index')"
            action-label="Buka data warga"
        />
    @elseif(($chart['known'] ?? 0) === 0)
        <div class="lw-panel-chart-card">
            <p class="lw-panel-chart-note m-0">
                Terdapat {{ $chart['total'] }} kepala keluarga aktif, tetapi jenis kelamin belum diisi.
                Lengkapi data di menu data warga.
            </p>
        </div>
    @else
        @php
            $malePct = (float) ($chart['male_percent'] ?? 0);
            $femalePct = (float) ($chart['female_percent'] ?? 0);
            $ariaSummary = sprintf(
                'Grafik lingkaran: Laki-laki %s persen (%d KK), Perempuan %s persen (%d KK).',
                number_format($malePct, 1, ',', '.'),
                $chart['male'] ?? 0,
                number_format($femalePct, 1, ',', '.'),
                $chart['female'] ?? 0,
            );
        @endphp
        <div class="lw-panel-chart-card">
            <div class="lw-panel-chart-layout">
                <div
                    class="lw-panel-pie lw-panel-pie--gender"
                    role="img"
                    aria-label="{{ $ariaSummary }}"
                    style="--pie-male-end: {{ $malePct }};"
                ></div>
                <ul class="lw-panel-pie-legend">
                    <li class="lw-panel-pie-legend-item">
                        <span class="lw-panel-pie-swatch lw-panel-pie-swatch--male" aria-hidden="true"></span>
                        <span class="lw-panel-pie-legend-body">
                            <span class="lw-panel-pie-legend-label">Laki-laki</span>
                            <span class="lw-panel-pie-legend-value">{{ number_format($malePct, 1, ',', '.') }}% · {{ $chart['male'] }} KK</span>
                        </span>
                    </li>
                    <li class="lw-panel-pie-legend-item">
                        <span class="lw-panel-pie-swatch lw-panel-pie-swatch--female" aria-hidden="true"></span>
                        <span class="lw-panel-pie-legend-body">
                            <span class="lw-panel-pie-legend-label">Perempuan</span>
                            <span class="lw-panel-pie-legend-value">{{ number_format($femalePct, 1, ',', '.') }}% · {{ $chart['female'] }} KK</span>
                        </span>
                    </li>
                </ul>
            </div>
            @if(($chart['unknown'] ?? 0) > 0)
                <p class="lw-panel-chart-note lw-mt-4 m-0">
                    {{ $chart['unknown'] }} kepala keluarga tanpa data jenis kelamin tidak dimasukkan dalam grafik.
                </p>
            @endif
        </div>
    @endif
</section>
