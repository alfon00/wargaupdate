@props([
    'analytics' => [],
])

@php
    $population = $analytics['population'] ?? [];
    $education = $analytics['education'] ?? [];
    $gender = $analytics['gender'] ?? [];

    $total = (int) ($population['total'] ?? 0);
    $households = (int) ($population['households'] ?? 0);
    $classifiedPercent = (float) ($population['classified_percent'] ?? 0);

    $eduBuckets = ['TK', 'SD', 'SLTP', 'SLTA', 'PT'];
    $eduMaxRaw = (int) ($education['max'] ?? 0);
    $eduScaleMax = $eduMaxRaw > 0 ? max($eduMaxRaw, 1) : 1;

    $malePct = (float) ($gender['male_percent'] ?? 0);
    $femalePct = (float) ($gender['female_percent'] ?? 0);
    $knownGender = (int) ($gender['known'] ?? 0);
    $genderUnknown = (int) ($gender['unknown'] ?? 0);
    $maleCount = (int) ($gender['L'] ?? 0);
    $femaleCount = (int) ($gender['P'] ?? 0);
@endphp

<section class="lw-rt-analytics" aria-label="Ringkasan analitik kependudukan RT">
    <div class="lw-rt-analytics-grid">
        <article class="lw-rt-analytics-card">
            <h2 class="lw-rt-analytics-card-title">Penduduk</h2>
            @if($total === 0)
                <div class="lw-rt-analytics-chart lw-rt-analytics-chart--empty">
                    <div class="lw-rt-analytics-pie lw-rt-analytics-pie--empty" role="img" aria-label="Belum ada data penduduk"></div>
                    <p class="lw-rt-analytics-empty-text">Belum ada warga aktif</p>
                </div>
            @else
                <div class="lw-rt-analytics-chart">
                    <div
                        class="lw-rt-analytics-pie lw-rt-analytics-pie--population"
                        role="img"
                        aria-label="Penduduk terklasifikasi {{ number_format($classifiedPercent, 1, ',', '.') }} persen dari {{ $total }} jiwa"
                        style="--pie-classified-end: {{ $classifiedPercent }};"
                    ></div>
                    <p class="lw-rt-analytics-summary">{{ number_format($total, 0, ',', '.') }} jiwa · {{ number_format($households, 0, ',', '.') }} KK</p>
                    <ul class="lw-rt-analytics-legend">
                        <li class="lw-rt-analytics-legend-item">
                            <span class="lw-rt-analytics-swatch lw-rt-analytics-swatch--green" aria-hidden="true"></span>
                            <span>Terklasifikasi ({{ number_format($population['classified'] ?? 0, 0, ',', '.') }})</span>
                        </li>
                        <li class="lw-rt-analytics-legend-item">
                            <span class="lw-rt-analytics-swatch lw-rt-analytics-swatch--gray" aria-hidden="true"></span>
                            <span>Belum lengkap ({{ number_format($population['unclassified'] ?? 0, 0, ',', '.') }})</span>
                        </li>
                    </ul>
                </div>
            @endif
        </article>

        <article class="lw-rt-analytics-card">
            <h2 class="lw-rt-analytics-card-title">Tingkat Pendidikan</h2>
            @if($total === 0)
                <div class="lw-rt-analytics-chart lw-rt-analytics-chart--empty">
                    <div class="lw-rt-analytics-bars lw-rt-analytics-bars--empty" role="img" aria-label="Belum ada data pendidikan">
                        @foreach($eduBuckets as $bucket)
                            <div class="lw-rt-analytics-bar-col">
                                <div class="lw-rt-analytics-bar lw-rt-analytics-bar--empty"></div>
                                <span class="lw-rt-analytics-bar-label">{{ $bucket }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="lw-rt-analytics-empty-text">Belum ada warga aktif</p>
                </div>
            @elseif($eduMaxRaw === 0)
                <div class="lw-rt-analytics-chart lw-rt-analytics-chart--empty">
                    <div class="lw-rt-analytics-bars lw-rt-analytics-bars--empty" role="img" aria-label="Data pendidikan belum diisi">
                        @foreach($eduBuckets as $bucket)
                            <div class="lw-rt-analytics-bar-col">
                                <div class="lw-rt-analytics-bar lw-rt-analytics-bar--empty"></div>
                                <span class="lw-rt-analytics-bar-label">{{ $bucket }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="lw-rt-analytics-empty-text lw-rt-analytics-empty-text--partial">Data pendidikan belum diisi</p>
                </div>
            @else
                <div class="lw-rt-analytics-chart">
                    <div class="lw-rt-analytics-bars" role="img" aria-label="Grafik tingkat pendidikan warga aktif">
                        @foreach($eduBuckets as $bucket)
                            @php $count = (int) ($education[$bucket] ?? 0); @endphp
                            <div class="lw-rt-analytics-bar-col">
                                <div
                                    class="lw-rt-analytics-bar"
                                    style="height: {{ max(8, round($count / $eduScaleMax * 100)) }}%;"
                                    aria-label="{{ $bucket }}: {{ $count }} jiwa"
                                ></div>
                                <span class="lw-rt-analytics-bar-label">{{ $bucket }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </article>

        <article class="lw-rt-analytics-card">
            <h2 class="lw-rt-analytics-card-title">Jenis kelamin</h2>
            @if($total === 0)
                <div class="lw-rt-analytics-chart lw-rt-analytics-chart--empty">
                    <div class="lw-rt-analytics-donut-wrap">
                        <div class="lw-rt-analytics-donut lw-rt-analytics-donut--empty" role="img" aria-label="Belum ada data jenis kelamin"></div>
                    </div>
                    <p class="lw-rt-analytics-empty-text">Belum ada warga aktif</p>
                </div>
            @elseif($knownGender === 0)
                <div class="lw-rt-analytics-chart lw-rt-analytics-chart--empty">
                    <div class="lw-rt-analytics-donut-wrap">
                        <div class="lw-rt-analytics-donut lw-rt-analytics-donut--empty" role="img" aria-label="Jenis kelamin belum diisi"></div>
                    </div>
                    <p class="lw-rt-analytics-empty-text lw-rt-analytics-empty-text--partial">
                        Terdapat {{ number_format($total, 0, ',', '.') }} warga aktif, jenis kelamin belum diisi. Lengkapi di menu data warga.
                    </p>
                </div>
            @else
                <div class="lw-rt-analytics-chart">
                    <div class="lw-rt-analytics-donut-wrap">
                        <div
                            class="lw-rt-analytics-donut"
                            role="img"
                            aria-label="Rasio gender: Laki-laki {{ number_format($malePct, 1, ',', '.') }} persen, Perempuan {{ number_format($femalePct, 1, ',', '.') }} persen"
                            style="--donut-male-end: {{ $malePct }};"
                        ></div>
                        <span class="lw-rt-analytics-donut-center">{{ $maleCount }} : {{ $femaleCount }}</span>
                    </div>
                    <ul class="lw-rt-analytics-legend">
                        <li class="lw-rt-analytics-legend-item">
                            <span class="lw-rt-analytics-swatch lw-rt-analytics-swatch--male" aria-hidden="true"></span>
                            <span>Laki-laki ({{ number_format($malePct, 1, ',', '.') }}%)</span>
                        </li>
                        <li class="lw-rt-analytics-legend-item">
                            <span class="lw-rt-analytics-swatch lw-rt-analytics-swatch--female" aria-hidden="true"></span>
                            <span>Perempuan ({{ number_format($femalePct, 1, ',', '.') }}%)</span>
                        </li>
                    </ul>
                    @if($genderUnknown > 0)
                        <p class="lw-rt-analytics-partial-note">
                            {{ number_format($genderUnknown, 0, ',', '.') }} warga tanpa data jenis kelamin tidak dimasukkan dalam grafik.
                        </p>
                    @endif
                </div>
            @endif
        </article>
    </div>
</section>
