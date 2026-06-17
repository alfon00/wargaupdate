@extends('layouts.app')

@section('title', 'Verifikasi identitas')

@section('content')
<section class="lw-services-section lw-band--alt">
    <div class="lw-container lw-container--narrow">
    <a href="{{ route('services.show', $service) }}" class="lw-auth-back">← {{ $service->catalogLabel() }}</a>

    <header class="lw-auth-hub-head lw-auth-hub-head--compact">
        <p class="lw-section-tag">Layanan Surat</p>
        <h1 class="lw-section-title">Verifikasi identitas</h1>
        <p class="lw-auth-hub-lead">
            Masukkan NIK, pilih RT, dan nomor HP terdaftar untuk melanjutkan permohonan
            <strong>{{ $service->catalogLabel() }}</strong>.
        </p>
    </header>

    <div class="lw-alert lw-alert--info lw-mb-4" role="note">
        Jenis surat: <strong>{{ $service->catalogLabel() }}</strong>
    </div>

    @if(session('info'))
    <div class="lw-alert lw-alert--info lw-mb-4" role="status">{{ session('info') }}</div>
    @endif

    <div class="lw-form-card">
    <form id="surat-verify-form"
        method="POST"
        action="{{ route('services.surat.verify') }}"
        class="lw-form-stack">
        @csrf
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
            <label for="nik" class="lw-form-label">NIK (16 digit) <span class="lw-form-label-required">*</span></label>
            <input id="nik" name="nik" type="text" maxlength="16" inputmode="numeric" pattern="\d{16}" required
                value="{{ old('nik') }}" class="lw-form-input" autocomplete="off">
            @error('nik')<p class="lw-form-error">{{ $message }}</p>@enderror
        </div>
        <div class="lw-form-field">
            <label for="phone" class="lw-form-label">Nomor HP/ WhatsApp <span class="lw-form-label-required">*</span></label>
            <x-phone-input id="phone" name="phone" :value="old('phone')" class="lw-form-input" required />
            @error('phone')<p class="lw-form-error">{{ $message }}</p>@enderror
        </div>

        <div class="lw-form-actions">
            <button type="submit" class="lw-btn-primary w-full">Lanjut ke formulir permohonan</button>
        </div>
    </form>
    </div>

    <form method="POST" action="{{ route('services.surat.logout') }}" class="lw-mt-4">
        @csrf
        <button type="submit" class="lw-btn-secondary lw-btn-secondary--sm">Batalkan / pilih jenis lain</button>
    </form>

    <div class="lw-form-callout lw-mt-4">
        <p class="lw-form-callout-title lw-mb-0">Belum terdata di sistem?</p>
        <p class="lw-form-hint lw-mt-2 lw-mb-0">
            Hubungi pengurus RT untuk pencatatan. Jika sudah terdaftar, lakukan
            <a href="{{ route('services.pendataan-ulang') }}" class="lw-inline-link">pendataan ulang</a>
            lalu kembali pilih jenis surat dan klik Ajukan.
        </p>
    </div>
    </div>
</section>
@endsection
