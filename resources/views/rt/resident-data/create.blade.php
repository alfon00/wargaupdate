@extends('layouts.panel')

@section('title', 'Daftar KK & warga')

@php
    $oldMembers = array_values(old('members', []));
    if ($oldMembers === []) {
        $oldMembers = [[]];
    }
@endphp

@section('content')
<div class="lw-rt-page">
@include('rt.partials.page-head', [
    'eyebrow' => $rt->displayName(),
    'title' => 'Daftar KK & warga',
    'lead' => 'Satu formulir untuk kartu keluarga, kepala KK, dan anggota tambahan.',
])

<p class="lw-mb-4">
    <a href="{{ route('rt.data-warga.index') }}" class="lw-panel-page-back">← Kembali ke data warga</a>
</p>

<p class="lw-panel-field-hint lw-mb-4">Field bertanda <span class="lw-form-label-required">*</span> wajib diisi.</p>

<div data-rt-registration-page>
    <form method="POST" action="{{ route('rt.data-warga.store') }}"
        enctype="multipart/form-data"
        class="lw-panel-form lw-panel-form--wide lw-panel-form--labeled lw-rt-reg-form"
        data-household-registration-form
        data-rt-registration-form
        data-registration-ui="panel"
        data-include-member-documents="0"
        data-milik-sendiri-value="{{ \App\Support\HouseholdHousingOptions::STATUS_MILIK_SENDIRI }}"
        data-max-members="{{ $maxMembers }}"
        data-demographics='@json($demographics)'
        data-old-members='@json($oldMembers)'
        data-validation-errors='@json($errors->getMessages())'>
        @csrf

        <fieldset class="lw-panel-form-fieldset">
            <legend class="lw-panel-form-legend">Data kartu keluarga</legend>
            <div class="lw-panel-form-grid lw-panel-form-grid--labeled">
                <div class="lw-panel-field">
                    <label class="lw-panel-field-label">No. KK (16 digit) <span class="lw-form-label-required">*</span></label>
                    <input name="family_card_number" type="text" maxlength="16" inputmode="numeric" pattern="\d{16}"
                        value="{{ old('family_card_number') }}" class="lw-panel-field-input" required>
                    @error('family_card_number')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="lw-panel-field">
                    <label class="lw-panel-field-label">No. rumah</label>
                    <input name="house_number" type="text" value="{{ old('house_number') }}" class="lw-panel-field-input" maxlength="20">
                    @error('house_number')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="lw-panel-field lw-panel-field--span2">
                    <label class="lw-panel-field-label">Alamat tempat tinggal <span class="lw-form-label-required">*</span></label>
                    <textarea name="address" rows="2" required class="lw-panel-field-input">{{ old('address') }}</textarea>
                    <p class="lw-panel-field-hint">Isi sesuai alamat pada Kartu Keluarga atau KTP/KIA.</p>
                    @error('address')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>

                @include('public.services._household-recap-fields', ['household' => null, 'required' => true, 'context' => 'panel'])

                <div class="lw-panel-field">
                    <label class="lw-panel-field-label">Nomor HP/ WhatsApp <span class="lw-form-label-required">*</span></label>
                    <x-phone-input name="phone" :value="old('phone')" class="lw-panel-field-input" required />
                    @error('phone')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="lw-panel-field lw-rt-reg-form__check-row">
                    @include('partials.whatsapp-notify-locked', [
                        'checkClass' => 'lw-panel-check',
                    ])
                </div>
                <p class="lw-panel-field-hint lw-panel-field--span2">Pekerjaan, pendidikan, agama, dan status perkawinan diisi per anggota di bawah.</p>
            </div>
        </fieldset>

        <fieldset class="lw-panel-form-fieldset">
            <legend class="lw-panel-form-legend">Anggota keluarga</legend>
            <div class="lw-rt-reg-members-toolbar">
                <p class="lw-panel-field-hint lw-panel-field-hint--flush" data-member-count-label>1 anggota (kepala KK)</p>
                <button type="button" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm" data-add-member-btn>+ Tambah anggota</button>
            </div>
            <p class="lw-panel-field-hint is-hidden" data-member-max-msg></p>
            @error('members')<p class="lw-form-error">{{ $message }}</p>@enderror

            <div class="lw-rt-reg-members" data-members-container></div>
        </fieldset>

        <fieldset class="lw-panel-form-fieldset">
            <legend class="lw-panel-form-legend">Lampiran berkas</legend>
            <p class="lw-panel-field-hint lw-mb-4">PDF/JPG/PNG, maks. 5 MB per berkas.</p>
            <div class="lw-panel-form-grid lw-panel-form-grid--labeled">
                <div class="lw-panel-field">
                    <label class="lw-panel-field-label">Scan/foto KK <span class="lw-form-label-required">*</span></label>
                    <input type="file" name="document_kk" class="lw-panel-field-input" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="lw-panel-field-hint">Arsip dokumen keluarga.</p>
                    @error('document_kk')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="lw-panel-field">
                    <label class="lw-panel-field-label">Scan/foto KTP kepala KK <span class="lw-form-label-required">*</span></label>
                    <input type="file" name="document_ktp" class="lw-panel-field-input" accept=".pdf,.jpg,.jpeg,.png" required>
                    <p class="lw-panel-field-hint">Referensi wajah surat online. Foto JPG/PNG yang jelas.</p>
                    @error('document_ktp')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
                <div class="lw-panel-field lw-panel-field--span2">
                    <label class="lw-panel-field-label">Lampiran tambahan (opsional)</label>
                    <input type="file" name="documents[]" class="lw-panel-field-input" accept=".pdf,.jpg,.jpeg,.png" multiple>
                    <p class="lw-panel-field-hint">Berkas pendukung lain. KTP/KIA anggota lain dilengkapi lewat Edit warga.</p>
                    @error('documents')<p class="lw-form-error">{{ $message }}</p>@enderror
                    @error('documents.*')<p class="lw-form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </fieldset>

        <div class="lw-panel-form-actions">
            <button type="submit" class="lw-panel-btn">Simpan KK &amp; warga</button>
            <a href="{{ route('rt.data-warga.index') }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
        </div>
    </form>
</div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/rt-household-registration.js'])
@endpush
