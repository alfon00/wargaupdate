{{-- Expects $analytics, $monograph, $populationResidentsActive --}}
<section class="lw-rt-analytics lw-admin-population-analytics" aria-labelledby="admin-population-analytics-heading">
    <h2 id="admin-population-analytics-heading" class="lw-panel-section-title">Monitoring kependudukan</h2>
    <x-rt.population-analytics-widgets :analytics="$analytics" />
</section>

<section class="lw-rt-monograph lw-admin-population-monograph" aria-labelledby="admin-monograph-heading">
    <div class="lw-panel-section-head lw-rt-dashboard-monograph-head">
        <div>
            <h2 id="admin-monograph-heading" class="lw-panel-section-title lw-panel-section-title--flush">Monografi kependudukan</h2>
            <p class="lw-panel-field-hint lw-mb-0">Rekap seluruh RT — monitoring admin.</p>
        </div>
    </div>
    <x-rt.population-monograph-table :monograph="$monograph" />
    @if(($populationResidentsActive ?? 0) === 0)
        <p class="lw-panel-field-hint lw-mt-3 lw-mb-0">Belum ada warga aktif — angka monografi ditampilkan sebagai «—» sampai data didaftarkan.</p>
    @endif
</section>
