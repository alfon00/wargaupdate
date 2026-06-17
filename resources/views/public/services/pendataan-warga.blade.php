@extends('layouts.app')

@section('title', 'Pendataan warga')

@section('content')
<section class="lw-services-section lw-band--alt">
    <div class="lw-container lw-container--narrow">
    <a href="{{ route('services.index') }}" class="lw-auth-back">← Semua layanan</a>

    <header class="lw-auth-hub-head lw-auth-hub-head--compact">
        <p class="lw-section-tag">Layanan</p>
        <h1 class="lw-section-title">Pendataan warga</h1>
        <p class="lw-auth-hub-lead">
            Untuk keluarga yang belum terdata di RT. Isi data sesuai Kartu Keluarga, unggah KK serta KTP/KIA setiap anggota,
            lalu pengurus RT akan memverifikasi berkas sebelum data dinyatakan aktif.
        </p>
    </header>

    <x-service-requirements :items="config('kelurahan.layanan_persyaratan.pendataan_warga', [])" class="lw-mb-4" />

    <p class="lw-form-hint lw-mb-3">Field bertanda <span class="lw-form-label-required">*</span> wajib diisi.</p>

    <div data-pendataan-warga-page>
        <form method="POST" action="{{ route('services.pendataan-warga.store') }}"
            enctype="multipart/form-data"
            class="lw-form-card lw-form-stack lw-form--labeled lw-pendataan-warga-form"
            data-household-registration-form
            data-registration-ui="public"
            data-include-member-documents="1"
            data-milik-sendiri-value="{{ \App\Support\HouseholdHousingOptions::STATUS_MILIK_SENDIRI }}"
            data-max-members="{{ $maxMembers }}"
            data-demographics='@json($demographics)'
            data-old-members='@json($oldMembers)'
            data-validation-errors='@json($errors->getMessages())'>
            @csrf

            <fieldset class="lw-form-fieldset lw-form-section--first">
                <legend class="lw-form-legend">RT domisili</legend>
                <div class="lw-form-grid lw-form-grid--labeled">
                    <div class="lw-form-field lw-form-field--span2">
                        <label for="rt_profile_id" class="lw-form-label">RT <span class="lw-form-label-required">*</span></label>
                        <select id="rt_profile_id" name="rt_profile_id" required class="lw-form-select">
                            <option value="">— Pilih RT —</option>
                            @foreach($rtProfiles as $rt)
                                <option value="{{ $rt->id }}" @selected(old('rt_profile_id') == $rt->id)>{{ $rt->displayName() }}</option>
                            @endforeach
                        </select>
                        @error('rt_profile_id')<p class="lw-form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="lw-form-fieldset lw-form-section">
                <legend class="lw-form-legend">Data kartu keluarga</legend>
                <p class="lw-form-hint lw-mb-3">Lengkapi data keluarga sesuai dokumen KK dan KTP/KIA yang akan diunggah.</p>
                <div class="lw-form-grid lw-form-grid--labeled">
                    <div class="lw-form-field">
                        <label for="family_card_number" class="lw-form-label">No. KK (16 digit) <span class="lw-form-label-required">*</span></label>
                        <input id="family_card_number" name="family_card_number" type="text" maxlength="16" inputmode="numeric" pattern="\d{16}"
                            value="{{ old('family_card_number') }}" class="lw-form-input" required>
                        @error('family_card_number')<p class="lw-form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="lw-form-field">
                        <label for="house_number" class="lw-form-label">No. rumah</label>
                        <input id="house_number" name="house_number" type="text" value="{{ old('house_number') }}" class="lw-form-input" maxlength="20">
                        @error('house_number')<p class="lw-form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="lw-form-field lw-form-field--span2">
                        <label for="address" class="lw-form-label">Alamat tempat tinggal <span class="lw-form-label-required">*</span></label>
                        <textarea id="address" name="address" rows="2" required class="lw-form-input">{{ old('address') }}</textarea>
                        <p class="lw-form-hint">Isi sesuai alamat pada Kartu Keluarga atau KTP/KIA.</p>
                        @error('address')<p class="lw-form-error">{{ $message }}</p>@enderror
                    </div>

                    @include('public.services._household-recap-fields', ['household' => null, 'required' => true, 'context' => 'public'])

                    <div class="lw-form-field">
                        <label for="phone" class="lw-form-label">Nomor HP/ WhatsApp <span class="lw-form-label-required">*</span></label>
                        <x-phone-input id="phone" name="phone" :value="old('phone')" class="lw-form-input" required />
                        @error('phone')<p class="lw-form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="lw-form-field lw-form-field--check-row">
                        @include('partials.whatsapp-notify-locked')
                    </div>
                </div>
            </fieldset>

            <fieldset class="lw-form-fieldset lw-form-section">
                <legend class="lw-form-legend">Anggota keluarga</legend>
                <div class="lw-pendataan-members-toolbar lw-mb-3">
                    <p class="lw-form-hint lw-mb-0" data-member-count-label>1 anggota (kepala KK)</p>
                    <button type="button" class="lw-btn-secondary lw-btn-secondary--sm" data-add-member-btn>+ Tambah anggota</button>
                </div>
                <p class="lw-form-hint is-hidden" data-member-max-msg></p>
                @error('members')<p class="lw-form-error">{{ $message }}</p>@enderror
                <div class="lw-form-stack" data-members-container></div>
            </fieldset>

            <fieldset class="lw-form-fieldset lw-form-section">
                <legend class="lw-form-legend">Verifikasi wajah kepala keluarga</legend>
                <p class="lw-form-hint lw-mb-0">
                    Ambil foto selfie kepala keluarga langsung di kamera. Wajah harus terdeteksi jelas.
                </p>
                @include('partials.face-capture-widget')
            </fieldset>

            <fieldset class="lw-form-fieldset lw-form-section">
                <legend class="lw-form-legend">Lampiran berkas</legend>
                <p class="lw-form-hint lw-mb-4">Unggah scan/foto KK. PDF/JPG/PNG, maks. 5 MB. KTP/KIA setiap anggota diunggah pada kartu anggota di atas.</p>
                <div class="lw-form-field">
                    <label for="document_kk" class="lw-form-label">Scan/foto Kartu Keluarga (KK) <span class="lw-form-label-required">*</span></label>
                    <input id="document_kk" name="document_kk" type="file" class="lw-form-input lw-form-file" accept=".pdf,.jpg,.jpeg,.png" required>
                    @error('document_kk')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
            </fieldset>

            <div class="lw-form-actions">
                <button type="submit" class="lw-btn-primary">Kirim pendataan warga</button>
            </div>
        </form>
    </div>
    </div>
</section>
@endsection

@push('scripts')
    @vite(['resources/js/pendataan-warga.js', 'resources/js/pendataan-warga-face.js'])
@endpush
