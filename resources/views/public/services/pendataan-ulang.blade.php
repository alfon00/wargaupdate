@extends('layouts.app')

@section('title', 'Pendataan ulang')

@section('content')
@php
    $oldMembers = array_values(old('members', $members));
    if ($oldMembers === []) {
        $oldMembers = $members;
    }
@endphp
<section class="lw-services-section lw-band--alt">
    <div class="lw-container lw-container--narrow">
    <a href="{{ route('services.index') }}" class="lw-auth-back">← Semua layanan</a>

    <header class="lw-auth-hub-head lw-auth-hub-head--compact">
        <p class="lw-section-tag">Layanan</p>
        <h1 class="lw-section-title">Pendataan ulang</h1>
        <p class="lw-auth-hub-lead">
            Untuk warga yang sudah terdata. Verifikasi identitas, lalu unggah scan KK serta KTP/KIA setiap anggota keluarga.
            Pengurus RT akan memeriksa berkas dan memperbarui data lewat panel verifikasi.
        </p>
    </header>

    <x-service-requirements :items="config('kelurahan.layanan_persyaratan.pendataan_ulang', [])" class="lw-mb-4" />

    @if(! $resident)
        <p class="lw-form-hint lw-mb-3">Field bertanda <span class="lw-form-label-required">*</span> wajib diisi.</p>
        <div class="lw-form-card">
        <form method="POST" action="{{ route('services.pendataan-ulang.verify') }}" class="lw-form-stack lw-form--labeled">
            @csrf
            <div class="lw-form-grid lw-form-grid--labeled">
                <div class="lw-form-field">
                    <label for="rt_profile_id" class="lw-form-label">RT <span class="lw-form-label-required">*</span></label>
                    <select id="rt_profile_id" name="rt_profile_id" required class="lw-form-select">
                        <option value="">— Pilih RT —</option>
                        @foreach($rtProfiles as $rt)
                            <option value="{{ $rt->id }}" @selected(old('rt_profile_id') == $rt->id)>{{ $rt->displayName() }}</option>
                        @endforeach
                    </select>
                    @error('rt_profile_id')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="lw-form-field">
                    <label for="phone" class="lw-form-label">Nomor HP/ WhatsApp <span class="lw-form-label-required">*</span></label>
                    <x-phone-input id="phone" name="phone" :value="old('phone')" class="lw-form-input" required />
                    @error('phone')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="lw-form-field lw-form-field--span2">
                    <label for="nik" class="lw-form-label">NIK kepala KK (16 digit) <span class="lw-form-label-required">*</span></label>
                    <input id="nik" name="nik" type="text" maxlength="16" inputmode="numeric" pattern="\d{16}" required
                        value="{{ old('nik') }}" class="lw-form-input">
                    @error('nik')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="lw-form-actions">
                <button type="submit" class="lw-btn-primary">Verifikasi &amp; lanjut</button>
            </div>
        </form>
        </div>
    @else
        <div class="lw-alert lw-alert--success lw-mb-4">
            Identitas terverifikasi: <strong>{{ $resident->name }}</strong> — KK {{ $resident->household?->family_card_number ?: '—' }}.
        </div>

        <div data-pendataan-ulang-page>
        <form method="POST" action="{{ route('services.pendataan-ulang.store') }}"
            enctype="multipart/form-data"
            class="lw-form-card lw-form-stack lw-pendataan-ulang-form lw-form--labeled"
            data-pendataan-ulang-form
            data-old-members='@json($oldMembers)'>
            @csrf

            <fieldset class="lw-form-fieldset lw-form-section--first">
                <legend class="lw-form-legend">Lampiran berkas</legend>
                <p class="lw-form-hint lw-mb-4">Unggah scan/foto KK terbaru. PDF/JPG/PNG, maks. 5 MB per berkas.</p>
                <div class="lw-form-field">
                    <label for="document_kk" class="lw-form-label">Scan/foto Kartu Keluarga (KK) <span class="lw-form-label-required">*</span></label>
                    <input id="document_kk" name="document_kk" type="file" class="lw-form-input lw-form-file" accept=".pdf,.jpg,.jpeg,.png" required>
                    @error('document_kk')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
            </fieldset>

            <fieldset class="lw-form-fieldset lw-form-section">
                <legend class="lw-form-legend">KTP / KIA anggota keluarga</legend>
                <p class="lw-form-hint lw-mb-3" data-member-count-label>Memuat anggota…</p>
                @error('members')<p class="lw-form-error">{{ $message }}</p>@enderror
                <div class="lw-form-stack" data-members-container></div>
            </fieldset>

            <fieldset class="lw-form-fieldset lw-form-section">
                <legend class="lw-form-legend">Notifikasi</legend>
                <div class="lw-form-field">
                    @include('partials.whatsapp-notify-locked')
                </div>
            </fieldset>

            <div class="lw-form-actions">
                <button type="submit" class="lw-btn-primary">Kirim pendataan ulang</button>
            </div>
        </form>
        </div>
    @endif
    </div>
</section>
@endsection

@if($resident)
@push('scripts')
    @vite(['resources/js/pendataan-ulang.js'])
@endpush
@endif
