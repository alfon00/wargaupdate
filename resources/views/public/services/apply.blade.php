@extends('layouts.app')

@section('title', 'Ajukan — '.$service->catalogLabel())

@section('content')
<section class="lw-services-section lw-band--alt">
    <div class="lw-container lw-container--narrow">
<a href="{{ route('services.show', $service) }}" class="lw-auth-back">← {{ $service->catalogLabel() }}</a>

<header class="lw-auth-hub-head lw-auth-hub-head--compact">
    <p class="lw-section-tag">Permohonan</p>
    <h1 class="lw-section-title">Ajukan surat pengantar</h1>
    <p class="lw-auth-hub-lead">{{ $service->catalogLabel() }} — tanpa login. Portal memfasilitasi permohonan; surat fisik dicetak di sekretariat RT, bukan dokumen resmi Dukcapil.</p>
    @if($resident)
    <div class="lw-alert lw-alert--success lw-mt-3" role="status">
        Identitas terverifikasi: <strong>{{ $resident->name }}</strong> (NIK {{ $resident->nik }}).
        Lengkapi formulir di bawah dan unggah berkas KK serta KTP/KIA pemohon.
    </div>
    @endif
    @if(! empty($profileIncomplete))
    <div class="lw-alert lw-alert--warn lw-mt-4" role="alert">
        <p class="lw-mb-0"><strong>Data kependudukan belum lengkap.</strong> Lengkapi melalui
            <a href="{{ route('services.pendataan-ulang') }}" class="lw-inline-link">Pendataan ulang</a>
            atau hubungi pengurus RT sebelum mengajukan surat.</p>
        @if(! empty($missingProfileLabels))
        <p class="lw-form-hint lw-mb-0 lw-mt-2">Belum diisi: {{ implode(', ', array_values($missingProfileLabels)) }}.</p>
        @endif
    </div>
    @endif
</header>

<div class="lw-form-card">
<form method="POST" action="{{ route('services.apply.store', $service) }}" enctype="multipart/form-data"
    class="lw-form-stack lw-form--labeled" data-surat-apply-form>
    @csrf

    <p class="lw-form-hint lw-mb-0">Field bertanda <span class="lw-form-label-required">*</span> wajib diisi.</p>

    @if($rtProfiles->isEmpty())
    <div class="lw-form-callout lw-form-callout--warn">
        Belum ada RT yang dapat menerima permohonan online (pengurus RT belum terdaftar).
        Hubungi kantor kelurahan atau ajukan surat langsung ke pengurus RT setempat.
    </div>
    @else
    <fieldset class="lw-form-fieldset lw-form-section--first">
        <legend class="lw-form-legend">Data pemohon</legend>
        <p class="lw-form-hint lw-mb-4">Pemohon adalah orang yang telah diverifikasi identitas. Data di bawah terisi otomatis dari sesi verifikasi.</p>
        @include('public.services._apply-pemohon-fields', ['rtProfiles' => $rtProfiles, 'resident' => $resident])
    </fieldset>

    @include('public.services._apply-documents-fields', ['service' => $service])
    @endif

    @if($service->code === 'surat_usaha')
    <fieldset class="lw-form-fieldset">
        <legend class="lw-form-legend">Data usaha</legend>
        <div class="lw-form-grid lw-form-grid--labeled">
            <div class="lw-form-field lw-form-field--span2">
                <label for="nama_usaha" class="lw-form-label">Nama usaha <span class="lw-form-label-required">*</span></label>
                <input id="nama_usaha" name="nama_usaha" type="text" required maxlength="255" class="lw-form-input"
                    value="{{ old('nama_usaha') }}">
                @error('nama_usaha')<p class="lw-form-error">{{ $message }}</p>@enderror
            </div>
            <div class="lw-form-field">
                <label for="jenis_usaha" class="lw-form-label">Jenis usaha <span class="lw-form-label-required">*</span></label>
                <input id="jenis_usaha" name="jenis_usaha" type="text" required maxlength="255" class="lw-form-input"
                    value="{{ old('jenis_usaha') }}">
                @error('jenis_usaha')<p class="lw-form-error">{{ $message }}</p>@enderror
            </div>
            <div class="lw-form-field lw-form-field--span2">
                <label for="alamat_usaha" class="lw-form-label">Alamat usaha <span class="lw-form-label-required">*</span></label>
                <textarea id="alamat_usaha" name="alamat_usaha" rows="2" required class="lw-form-textarea">{{ old('alamat_usaha', $resident->household?->address) }}</textarea>
                @error('alamat_usaha')<p class="lw-form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </fieldset>
    @endif

    <div class="lw-form-field">
        <label for="purpose" class="lw-form-label">Keperluan / keterangan <span class="lw-form-label-required">*</span></label>
        <textarea id="purpose" name="purpose" rows="3" required class="lw-form-textarea"
            placeholder="Jelaskan keperluan surat...">{{ old('purpose') }}</textarea>
        @error('purpose')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>

    <div class="lw-form-actions">
        <button type="submit" class="lw-btn-primary w-full"
            @disabled($rtProfiles->isEmpty() || ! empty($profileIncomplete))>
            Kirim Permohonan
        </button>
    </div>
</form>
</div>
@if($resident)
<form method="POST" action="{{ route('services.surat.logout') }}" class="lw-mt-4">
    @csrf
    <button type="submit" class="lw-btn-secondary lw-btn-secondary--sm">Batalkan permohonan</button>
</form>
@endif
<p class="lw-form-hint lw-form-hint--narrow">
    Kendala formulir? <a href="{{ route('contact.create') }}" class="lw-inline-link">Kirim laporan</a>.
</p>
    </div>
</section>
@endsection
