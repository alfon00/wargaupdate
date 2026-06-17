@extends('layouts.panel')

@section('title', 'Profil RT')

@section('content')
<div class="lw-admin-page">
@include('admin.partials.page-head', [
    'title' => 'Profil RT',
    'lead' => 'Kelola entitas RT di wilayah Inauga sebelum akun ketua/sekretaris ditautkan.',
])

<x-admin.page-toolbar
    :action-url="route('admin.rt-profiles.index')"
    :button-url="route('admin.rt-profiles.create')"
    button-label="+ Tambah profil RT"
>
    <div class="lw-admin-filter-field lw-admin-filter-field--grow">
        <label for="q">Cari RT, RW, atau ketua RT</label>
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Contoh: 008 atau nama ketua…">
    </div>
    <div class="lw-admin-filter-actions">
        <button type="submit" class="lw-panel-btn lw-panel-btn--sm">Terapkan</button>
        @if(request('q'))
            <a href="{{ route('admin.rt-profiles.index') }}" class="lw-panel-btn lw-panel-btn--ghost lw-panel-btn--sm">Reset</a>
        @endif
    </div>
</x-admin.page-toolbar>

@if($profiles->isEmpty())
    <x-admin.empty-state
        title="Belum ada profil RT"
        :description="request('q') ? 'Tidak ada RT yang cocok dengan pencarian.' : 'Tambahkan profil RT untuk wilayah kelurahan.'"
        :action-url="route('admin.rt-profiles.create')"
        action-label="+ Tambah profil RT"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table">
            <thead>
                <tr>
                    <th>RT</th>
                    <th>RW</th>
                    <th>Ketua RT</th>
                    <th>Pengurus terhubung</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($profiles as $profile)
                    <tr>
                        <td>{{ $profile->displayName() }}</td>
                        <td>{{ $profile->rw_number ?: '—' }}</td>
                        <td>{{ $profile->ketua_rt ?: '—' }}</td>
                        <td>
                            @if($profile->staff_count < 1)
                                <span class="lw-panel-profile-warn lw-panel-profile-warn--compact">0 — belum ada pengurus</span>
                            @else
                                {{ $profile->staff_count }}
                            @endif
                        </td>
                        <td class="whitespace-nowrap">
                            <div class="lw-admin-table-actions">
                                <a href="{{ route('admin.rt-profiles.edit', $profile) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Edit</a>
                                @if($profile->canBeDeletedByAdmin())
                                    @include('admin.partials.delete-form', [
                                        'action' => route('admin.rt-profiles.destroy', $profile),
                                        'confirm' => 'Hapus profil '.$profile->displayName().'? Tindakan ini tidak dapat dibatalkan.',
                                    ])
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-mt-4">{{ $profiles->links() }}</div>
@endif
</div>
@endsection
