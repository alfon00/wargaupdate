@extends('layouts.panel')

@section('title', 'Pengguna')

@section('content')
<div class="lw-admin-page">
@include('admin.partials.page-head', [
    'title' => 'Pengguna',
    'lead' => 'Buat akun pengurus RT atau kelurahan, lalu tautkan ketua/sekretaris RT ke profil RT yang sesuai.',
])

<x-admin.page-toolbar
    :action-url="route('admin.users.index')"
    :button-url="route('admin.users.create')"
    button-label="+ Tambah pengguna"
>
    <div class="lw-admin-filter-field lw-admin-filter-field--grow">
        <label for="q">Cari nama atau email</label>
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Ketik untuk mencari…">
    </div>
    <div class="lw-admin-filter-field">
        <label for="role">Peran</label>
        <select id="role" name="role">
            <option value="">Semua peran</option>
            @foreach($roles as $value => $roleLabel)
                <option value="{{ $value }}" @selected(request('role') === $value)>{{ $roleLabel }}</option>
            @endforeach
        </select>
    </div>
    <div class="lw-admin-filter-actions">
        <button type="submit" class="lw-panel-btn lw-panel-btn--sm">Terapkan</button>
        @if(request()->hasAny(['q', 'role']))
            <a href="{{ route('admin.users.index') }}" class="lw-panel-btn lw-panel-btn--ghost lw-panel-btn--sm">Reset</a>
        @endif
    </div>
</x-admin.page-toolbar>

@if($users->isEmpty())
    <x-admin.empty-state
        title="Belum ada pengguna ditemukan"
        :description="request()->hasAny(['q', 'role']) ? 'Coba ubah kata kunci atau reset filter.' : 'Buat akun pengurus RT atau kelurahan pertama.'"
        :action-url="route('admin.users.create')"
        action-label="+ Tambah pengguna"
    />
@else
    <div class="lw-panel-table-wrap">
        <table class="lw-panel-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th>RT</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><x-admin.role-badge :role="$user->role" /></td>
                        <td>
                            @if($user->rtProfile)
                                {{ $user->rtProfile->displayName() }}
                            @elseif(in_array($user->role?->value, ['ketua_rt', 'sekretaris_rt'], true))
                                <span class="lw-panel-profile-warn lw-panel-profile-warn--compact">Belum tertaut</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="whitespace-nowrap">
                            <div class="lw-admin-table-actions">
                                <a href="{{ route('admin.users.edit', $user) }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Edit</a>
                                @if($user->id !== auth()->id())
                                    @include('admin.partials.delete-form', [
                                        'action' => route('admin.users.destroy', $user),
                                        'confirm' => 'Hapus akun '.$user->name.'? Tindakan ini tidak dapat dibatalkan.',
                                    ])
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="lw-mt-4">{{ $users->links() }}</div>
@endif
</div>
@endsection
