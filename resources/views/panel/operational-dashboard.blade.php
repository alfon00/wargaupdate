<div class="lw-panel-stats lw-panel-stats--4">
    <x-panel.stat-card label="Warga terdata" :value="$stats['residents']" />
    <x-panel.stat-card label="Kartu keluarga" :value="$stats['households']" />
    <x-panel.stat-card label="Total permohonan" :value="$stats['applications']" />
    <x-panel.stat-card label="Menunggu proses" :value="$stats['pending']" variant="highlight" />
</div>

<section class="lw-panel-quick lw-kel-no-print" aria-labelledby="quick-heading-operational">
    <h2 id="quick-heading-operational" class="lw-panel-section-title">Akses cepat</h2>
    <div class="lw-panel-quick-grid">
        <x-panel.quick-card
            :href="route('kelurahan.applications.index')"
            title="Semua permohonan"
            description="Daftar permohonan surat seluruh RT — mode monitoring"
            :badge="$stats['pending'] > 0 ? ($stats['pending'].' menunggu') : null"
        />
        <x-panel.quick-card
            :href="route('kelurahan.population.index')"
            title="Data warga lengkap"
            description="Daftar warga seluruh RT — mode monitoring"
        />
        <x-panel.quick-card
            :href="route('kelurahan.reports.index')"
            title="Laporan warga"
            description="Kontak & keluhan dari portal publik"
            :badge="($stats['new_reports'] ?? 0) > 0 ? ($stats['new_reports'].' baru') : null"
        />
    </div>
</section>

<section class="lw-panel-section" aria-labelledby="recent-heading-operational">
    <div class="lw-panel-section-head">
        <h2 id="recent-heading-operational" class="lw-panel-section-title lw-panel-section-title--flush">Permohonan terbaru</h2>
        @if($recentApplications->isNotEmpty())
            <a href="{{ route('kelurahan.applications.index') }}" class="lw-panel-link">Lihat semua →</a>
        @endif
    </div>
    @if($recentApplications->isEmpty())
        <x-panel.empty-state
            title="Belum ada permohonan"
            description="Permohonan surat dari seluruh RT akan tampil di sini."
            :action-url="route('kelurahan.applications.index')"
            action-label="Buka daftar permohonan"
        />
    @else
        <div class="lw-panel-table-wrap">
            <table class="lw-panel-table">
                <thead>
                    <tr>
                        <th>No. permohonan</th>
                        <th>RT</th>
                        <th>Warga</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Surat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentApplications as $app)
                        <tr>
                            <td>
                                <a href="{{ route('kelurahan.applications.show', $app) }}" class="lw-panel-table-link">{{ $app->application_number }}</a>
                            </td>
                            <td>{{ $app->applicantRtLabel() }}</td>
                            <td>{{ $app->applicantName() }}</td>
                            <td>{{ $app->serviceType->name }}</td>
                            <td><span class="lw-badge {{ $app->status->badgeClass() }}">{{ $app->status->label() }}</span></td>
                            <td>
                                @if($app->hasManualLetterIssued())
                                    {{ $app->issuedLetterNumber() }}
                                @elseif($app->generatedLetter)
                                    PDF
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<section class="lw-panel-section lw-mt-6" aria-labelledby="reports-heading-operational">
    <div class="lw-panel-section-head">
        <h2 id="reports-heading-operational" class="lw-panel-section-title lw-panel-section-title--flush">Laporan warga terbaru</h2>
        @if($recentReports->isNotEmpty())
            <a href="{{ route('kelurahan.reports.index') }}" class="lw-panel-link">Lihat semua →</a>
        @endif
    </div>
    @if($recentReports->isEmpty())
        <x-panel.empty-state
            title="Belum ada laporan warga"
            description="Kontak dan pengaduan dari portal publik akan tampil di sini."
            :action-url="route('kelurahan.reports.index')"
            action-label="Buka daftar laporan"
        />
    @else
        <div class="lw-panel-table-wrap">
            <table class="lw-panel-table">
                <thead>
                    <tr>
                        <th>No. laporan</th>
                        <th>Perihal</th>
                        <th>Kategori</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentReports as $report)
                        <tr>
                            <td>
                                <a href="{{ route('kelurahan.reports.show', $report) }}" class="lw-panel-table-link">{{ $report->report_number }}</a>
                            </td>
                            <td>{{ $report->subject }}</td>
                            <td>{{ $report->categoryLabel() }}</td>
                            <td><span class="lw-badge {{ $report->status->badgeClass() }}">{{ $report->status->label() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
