@php
    /** @var \App\Models\Resident $resident */
    $household = $resident->household;
@endphp

<div class="lw-panel-table-wrap lw-rt-unified-member-profile">
    <table class="lw-panel-table lw-rt-resident-detail-table">
        <tbody>
            <tr class="lw-rt-resident-detail-section">
                <th colspan="2">Identitas warga</th>
            </tr>
            <tr>
                <th scope="row">Nama lengkap</th>
                <td>{{ $resident->name }}</td>
            </tr>
            <tr>
                <th scope="row">No. Kartu Keluarga</th>
                <td>{{ $household?->family_card_number ?: '—' }}</td>
            </tr>
            <tr>
                <th scope="row">NIK</th>
                <td>{{ $resident->nik ?: '—' }}</td>
            </tr>
            <tr>
                <th scope="row">Tempat, tanggal lahir</th>
                <td>{{ $resident->birthPlaceDate() ?: '—' }}</td>
            </tr>
            <tr>
                <th scope="row">Jenis kelamin</th>
                <td>{{ $resident->gender ?: '—' }}</td>
            </tr>
            <tr>
                <th scope="row">Alamat tempat tinggal</th>
                <td>{{ filled(trim($resident->fullAddress())) ? $resident->fullAddress() : '—' }}</td>
            </tr>
            <tr>
                <th scope="row">Agama</th>
                <td>{{ $resident->religion ?: '—' }}</td>
            </tr>
            <tr>
                <th scope="row">Status domisili</th>
                <td>
                    <span class="lw-badge {{ $resident->domicile_status?->badgeClass() }}">
                        {{ $resident->domicile_status?->label() ?? '—' }}
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">Kewarganegaraan</th>
                <td>{{ $resident->citizenship ?: '—' }}</td>
            </tr>

            <tr class="lw-rt-resident-detail-section">
                <th colspan="2">Sosial & pendidikan</th>
            </tr>
            <tr>
                <th scope="row">Pendidikan</th>
                <td>{{ $resident->education ?: '—' }}</td>
            </tr>
            <tr>
                <th scope="row">Pekerjaan</th>
                <td>{{ $resident->occupation ?: '—' }}</td>
            </tr>
            <tr>
                <th scope="row">Status perkawinan</th>
                <td>{{ $resident->marital_status ?: '—' }}</td>
            </tr>
            <tr>
                <th scope="row">Hubungan dalam KK</th>
                <td>{{ $resident->relationship_to_head ?: ($resident->is_head_of_family ? 'Kepala Keluarga' : '—') }}</td>
            </tr>

            <tr class="lw-rt-resident-detail-section">
                <th colspan="2">Kontak & sistem</th>
            </tr>
            <tr>
                <th scope="row">Nomor HP/ WhatsApp</th>
                <td>{{ $resident->phone ?: '—' }}</td>
            </tr>
            <tr>
                <th scope="row">Notifikasi WhatsApp</th>
                <td>
                    @if($resident->hasLatestWhatsappNotificationFailed())
                        <span class="lw-badge lw-badge--amber">Gagal terkirim</span>
                    @elseif($resident->whatsapp_notify)
                        <span class="lw-badge lw-badge--green">Aktif</span>
                    @else
                        <span class="lw-badge lw-badge--muted">Nonaktif</span>
                    @endif
                </td>
            </tr>
            @if($household)
                <tr>
                    <th scope="row">Kategori sumber</th>
                    <td>{{ $household->dataSourceLabel() }}</td>
                </tr>
            @endif

            @if($resident->verified_at)
                <tr>
                    <th scope="row">Diverifikasi RT</th>
                    <td>{{ $resident->verified_at->timezone('Asia/Jayapura')->format('d/m/Y H:i') }}
                        @if($resident->verifier) · {{ $resident->verifier->name }} @endif
                    </td>
                </tr>
            @endif
            @if($resident->verification_notes)
                <tr>
                    <th scope="row">Catatan verifikasi</th>
                    <td class="lw-pre-wrap">{{ $resident->verification_notes }}</td>
                </tr>
            @endif
            @if($resident->domicile_status?->isArchived())
                <tr>
                    <th scope="row">Tanggal arsip</th>
                    <td>{{ $resident->departed_at?->timezone('Asia/Jayapura')->format('d/m/Y') ?? '—' }}</td>
                </tr>
                @if($resident->departure_notes)
                    <tr>
                        <th scope="row">Catatan keluar</th>
                        <td class="lw-pre-wrap">{{ $resident->departure_notes }}</td>
                    </tr>
                @endif
            @endif
        </tbody>
    </table>
</div>
