@extends('layouts.panel')

@section('title', 'Permohonan')

@section('content')
<div class="lw-kel-page">
@include('kelurahan.partials.page-head', [
    'title' => 'Daftar Permohonan',
    'lead' => 'Tampilan monitoring seluruh RT — status tidak dapat diubah dari panel ini.',
])

<x-kelurahan.filter-bar :action="route('kelurahan.applications.index')" :reset-url="route('kelurahan.applications.index')">
    <x-kelurahan.filter-field label="RT" for="rt_profile_id">
        <select id="rt_profile_id" name="rt_profile_id">
            <option value="">Semua RT</option>
            @foreach($rtProfiles as $rt)
                <option value="{{ $rt->id }}" @selected(request('rt_profile_id') == $rt->id)>{{ $rt->displayName() }}</option>
            @endforeach
        </select>
    </x-kelurahan.filter-field>
    <x-kelurahan.filter-field label="Status" for="status">
        <select id="status" name="status">
            <option value="">Semua status</option>
            @foreach(\App\Enums\ApplicationStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </x-kelurahan.filter-field>
    <x-kelurahan.filter-field label="Tanggal dari" for="date_from">
        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
    </x-kelurahan.filter-field>
    <x-kelurahan.filter-field label="Tanggal sampai" for="date_to">
        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
    </x-kelurahan.filter-field>
    <x-kelurahan.filter-field label="Cari" for="q" class="sm:col-span-2">
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="No. permohonan atau nama warga">
    </x-kelurahan.filter-field>
</x-kelurahan.filter-bar>

@if($applications->isEmpty())
    <x-panel.empty-state
        title="Belum ada permohonan"
        :description="request()->hasAny(['q', 'status', 'rt_profile_id', 'date_from', 'date_to']) ? 'Coba ubah filter pencarian.' : 'Permohonan surat dari seluruh RT akan tampil di sini.'"
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
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Surat RT</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                    <tr>
                        <td>
                            <a href="{{ route('kelurahan.applications.show', $app) }}" class="lw-panel-table-link">{{ $app->application_number }}</a>
                        </td>
                        <td>{{ $app->applicantRtLabel() }}</td>
                        <td>{{ $app->applicantName() }}</td>
                        <td>{{ $app->serviceType->name }}</td>
                        <td>{{ $app->submitted_at?->format('d/m/Y') ?? '—' }}</td>
                        <td><span class="lw-badge {{ $app->status->badgeClass() }}">{{ $app->status->label() }}</span></td>
                        <td>
                            @if($app->hasManualLetterIssued())
                                <span class="lw-badge lw-badge--green">Sudah diterbitkan</span>
                                <span class="lw-panel-muted-note lw-ml-1">{{ $app->issuedLetterNumber() }}</span>
                            @elseif($app->generatedLetter)
                                <span class="lw-badge lw-badge--green">Sudah diterbitkan</span>
                                <a href="{{ route('kelurahan.applications.letter.print', $app) }}" target="_blank" rel="noopener" class="lw-panel-link lw-ml-2">Lihat surat</a>
                            @else
                                <span class="lw-badge lw-badge--muted">Belum</span>
                            @endif
                        </td>
                        <td class="lw-panel-table-actions">
                            <a href="{{ route('kelurahan.applications.show', $app) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-mt-4">{{ $applications->links() }}</div>
@endif
</div>
@endsection
