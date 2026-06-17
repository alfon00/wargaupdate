<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Data Warga RT</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        h1 { margin: 0 0 4px; font-size: 16px; }
        .meta { margin: 0 0 10px; color: #334155; }
        .block { margin: 0 0 12px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .head { margin: 0 0 6px; font-weight: 700; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #e2e8f0; font-size: 10px; text-transform: uppercase; letter-spacing: 0.03em; }
        .muted { color: #64748b; }
        ul { margin: 4px 0 0 16px; padding: 0; }
        li { margin: 0 0 2px; }
    </style>
</head>
<body>
    <h1>Laporan Data Warga RT</h1>
    <p class="meta">
        {{ $rt->displayName() }} · Dicetak {{ $generatedAt->format('d/m/Y H:i') }} WIT<br>
        Filter: {{ ucfirst($filter) }} · Kategori: {{ $kategori }}@if($search !== '') · Pencarian: "{{ $search }}" @endif
    </p>

    @forelse($households as $household)
        @php
            $head = $household->headResident;
            $docs = $household->pendataanDocuments;
        @endphp
        <section class="block">
            <p class="head">No. KK: {{ $household->family_card_number ?: '—' }} · Kepala: {{ $head?->name ?: '—' }}</p>
            <table>
                <tr>
                    <th style="width: 22%">Alamat</th>
                    <td>{{ $household->address ?: '—' }}</td>
                </tr>
                <tr>
                    <th>No. Rumah</th>
                    <td>{{ $household->house_number ?: '—' }}</td>
                </tr>
                <tr>
                    <th>Status Data</th>
                    <td>{{ $household->dataSourceLabel() }}</td>
                </tr>
            </table>

            <p class="head" style="margin-top:8px;">Anggota ({{ $household->residents->count() }})</p>
            <ul>
                @foreach($household->residents as $member)
                    <li>{{ $member->name }} ({{ $member->relationship_to_head ?: '-' }}) · {{ $member->domicile_status?->label() ?: '-' }}</li>
                @endforeach
            </ul>

            <p class="head" style="margin-top:8px;">Lampiran ({{ $docs->count() }})</p>
            @if($docs->isEmpty())
                <p class="muted">Tidak ada lampiran.</p>
            @else
                <ul>
                    @foreach($docs as $doc)
                        <li>{{ $doc->typeLabel() }} · {{ $doc->original_name ?: 'berkas' }} · {{ $doc->created_at?->format('d/m/Y') ?: '-' }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    @empty
        <p>Tidak ada data sesuai filter saat ini.</p>
    @endforelse
</body>
</html>
