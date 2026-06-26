<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Data Warga RT</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; }
        h1 { margin: 0 0 4px; font-size: 15px; }
        .meta { margin: 0 0 8px; color: #334155; line-height: 1.4; }
        .summary { margin: 0 0 10px; padding: 6px 8px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; }
        .family { margin: 0 0 14px; page-break-inside: avoid; }
        .family-head { margin: 0 0 6px; padding: 6px 8px; background: #e2e8f0; border: 1px solid #94a3b8; border-radius: 4px 4px 0 0; font-weight: 700; font-size: 10px; }
        .family-meta { margin: 0; padding: 0 8px 6px; border-left: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 3px 4px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 8px; text-transform: uppercase; letter-spacing: 0.02em; }
        .family-meta th { width: 18%; background: #f1f5f9; font-size: 8px; }
        .members { border-left: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; border-radius: 0 0 4px 4px; }
        .members > p { margin: 0; padding: 6px 8px 4px; font-weight: 700; font-size: 9px; }
        .muted { color: #64748b; }
        .empty { margin-top: 12px; color: #64748b; }
    </style>
</head>
<body>
    <h1>Laporan Data Warga RT — per Keluarga</h1>
    <p class="meta">
        {{ $rt->displayName() }} · Dicetak {{ $generatedAt->format('d/m/Y H:i') }} WIT<br>
        Filter: {{ ucfirst($filter) }} · Kategori: {{ $kategoriLabel }}@if($search !== '') · Pencarian: «{{ $search }}» @endif
    </p>

    <p class="summary">
        {{ $totalHouseholds }} kartu keluarga · {{ $totalResidents }} warga tercantum
    </p>

    @forelse($households as $index => $household)
        @php
            $head = $household->headResident
                ?? $household->residents->firstWhere('is_head_of_family', true);
            $members = $household->residents;
        @endphp
        <section class="family">
            <p class="family-head">
                Keluarga {{ $index + 1 }} · No. KK: {{ $household->family_card_number ?: '—' }}
                · Kepala: {{ $head?->name ?: '—' }}
            </p>

            <table class="family-meta">
                <tr>
                    <th>Alamat</th>
                    <td>{{ $household->address ?: '—' }}</td>
                    <th>No. rumah</th>
                    <td>{{ $household->house_number ?: '—' }}</td>
                </tr>
                <tr>
                    <th>Suku</th>
                    <td>{{ $household->suku ?: '—' }}</td>
                    <th>Kategori data</th>
                    <td>{{ $household->dataSourceLabel() }}</td>
                </tr>
                @if(filled($household->status_rumah_tinggal) || filled($household->kondisi_rumah_milik))
                    <tr>
                        <th>Status rumah</th>
                        <td>{{ \App\Support\HouseholdHousingOptions::statusLabel($household->status_rumah_tinggal) }}</td>
                        <th>Kondisi rumah</th>
                        <td>{{ \App\Support\HouseholdHousingOptions::kondisiLabel($household->kondisi_rumah_milik) }}</td>
                    </tr>
                @endif
            </table>

            <div class="members">
                <p>Anggota keluarga ({{ $members->count() }})</p>
                @if($members->isEmpty())
                    <p class="muted" style="padding: 0 8px 8px;">Tidak ada anggota sesuai filter.</p>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 3%">No</th>
                                <th style="width: 18%">Nama</th>
                                <th style="width: 12%">Hubungan</th>
                                <th style="width: 16%">NIK</th>
                                <th style="width: 20%">Tempat, tanggal lahir</th>
                                <th style="width: 5%">JK</th>
                                <th style="width: 10%">Pekerjaan</th>
                                <th style="width: 8%">Agama</th>
                                <th style="width: 8%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $memberIndex => $member)
                                <tr>
                                    <td>{{ $memberIndex + 1 }}</td>
                                    <td>
                                        {{ $member->name }}
                                        @if($member->is_head_of_family)
                                            (KK)
                                        @endif
                                    </td>
                                    <td>{{ $member->relationship_to_head ?: ($member->is_head_of_family ? 'Kepala Keluarga' : '—') }}</td>
                                    <td>{{ $member->nik ?: '—' }}</td>
                                    <td>{{ $member->birthPlaceDate() }}</td>
                                    <td>{{ $member->gender ?: '—' }}</td>
                                    <td>{{ $member->occupation ?: '—' }}</td>
                                    <td>{{ $member->religion ?: '—' }}</td>
                                    <td>{{ $member->domicile_status?->label() ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>
    @empty
        <p class="empty">Tidak ada data keluarga sesuai filter saat ini.</p>
    @endforelse
</body>
</html>
