@extends('layouts.panel')

@section('title', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
<div class="lw-admin-page">
@include('admin.partials.page-head', [
    'title' => ($user->exists ? 'Edit' : 'Tambah').' Pengguna',
    'lead' => $user->exists ? 'Perbarui data akun pengurus RT atau kelurahan.' : 'Buat akun pengurus RT (ketua) atau kelurahan.',
])

<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="lw-panel-form lw-panel-form--wide">
    @csrf
    @if($user->exists) @method('PUT') @endif

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Identitas akun</legend>
        <div class="lw-panel-field">
            <label for="name">Nama <span class="lw-form-label-required">*</span></label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="lw-panel-field">
            <label for="email_local">Email <span class="lw-form-label-required">*</span></label>
            <x-staff-email-input
                id="email_local"
                name="email_local"
                :value="old('email_local', \App\Support\StaffEmail::localPartForForm($user->email))"
                required
            />
            @error('email_local')
                <p class="lw-form-error">{{ $message }}</p>
            @enderror
            <p class="lw-panel-field-hint">Email login pengurus selalu menggunakan domain {{ \App\Support\StaffEmail::suffix() }}.</p>
        </div>
        <div class="lw-panel-field">
            <label for="password">Kata sandi @if(!$user->exists)<span class="lw-form-label-required">*</span>@endif</label>
            <input type="password" id="password" name="password" {{ $user->exists ? '' : 'required' }}>
            @if($user->exists)
                <p class="lw-panel-field-hint">Kosongkan jika tidak diubah.</p>
            @endif
        </div>
        <div class="lw-panel-field">
            <label for="phone">Telepon</label>
            <x-phone-input id="phone" name="phone" :value="old('phone', $user->phone)" />
        </div>
    </fieldset>

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Hak akses</legend>
        <div class="lw-panel-field">
            <label for="role">Peran <span class="lw-form-label-required">*</span></label>
            <select id="role" name="role" required>
                @foreach($roleGroups as $groupLabel => $groupRoles)
                    <optgroup label="{{ $groupLabel }}">
                        @foreach($groupRoles as $value => $roleLabel)
                            <option value="{{ $value }}" @selected(old('role', $user->role?->value) === $value)>{{ $roleLabel }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <p id="role-description" class="lw-panel-field-hint"></p>
        </div>
        <div class="lw-panel-field" id="rt-profile-field" hidden>
            <label for="rt_profile_id">Profil RT <span class="lw-form-label-required">*</span></label>
            <select id="rt_profile_id" name="rt_profile_id">
                <option value="">— Pilih RT —</option>
                @foreach($rtProfiles as $rt)
                    <option value="{{ $rt->id }}" @selected(old('rt_profile_id', $user->rt_profile_id) == $rt->id)>{{ $rt->displayName() }} — RW {{ $rt->rw_number ?: '—' }}</option>
                @endforeach
            </select>
            <p class="lw-panel-field-hint">Pilih RT 001–016. Wajib untuk akun Ketua RT agar profil panel tersinkron ke halaman Profil publik.</p>
        </div>
    </fieldset>

    <div class="lw-panel-form-actions">
        <button type="submit" class="lw-panel-btn">Simpan</button>
        <a href="{{ route('admin.users.index') }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
    </div>
</form>

@if($user->exists && $user->id !== auth()->id())
<section class="lw-panel-danger-zone mt-8" aria-labelledby="danger-zone-user">
    <h2 id="danger-zone-user" class="lw-panel-section-title text-red-800">Zona berbahaya</h2>
    <p class="text-sm text-slate-600 mb-3">Menghapus akun akan mencabut akses panel pengurus secara permanen.</p>
    @include('admin.partials.delete-form', [
        'action' => route('admin.users.destroy', $user),
        'confirm' => 'Hapus akun '.$user->name.'? Tindakan ini tidak dapat dibatalkan.',
    ])
</section>
@endif
</div>

@push('head')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var roleSelect = document.getElementById('role');
    var rtField = document.getElementById('rt-profile-field');
    var rtSelect = document.getElementById('rt_profile_id');
    var roleDescription = document.getElementById('role-description');
    var rtRoles = ['ketua_rt'];
    var roleDescriptions = @json($roleDescriptions);
    function toggleRt() {
        var show = rtRoles.indexOf(roleSelect.value) !== -1;
        rtField.hidden = !show;
        rtSelect.required = show;
        if (!show) {
            rtSelect.value = '';
        }
        if (roleDescription) {
            roleDescription.textContent = roleDescriptions[roleSelect.value] || '';
        }
    }
    roleSelect.addEventListener('change', toggleRt);
    toggleRt();
});
</script>
@endpush
@endsection
