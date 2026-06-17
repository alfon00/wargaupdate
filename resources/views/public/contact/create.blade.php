@extends('layouts.app')

@section('title', 'Pengaduan')

@section('content')
<div class="lw-contact-page lw-contact-split">
    @include('public.partials.contact.hero')

    <div class="lw-container lw-container--wide lw-contact-board">
        <div class="lw-track-hero-grid" aria-labelledby="contact-form-title">
            <aside class="lw-track-intro" aria-label="Informasi pengaduan">
                <h2 class="lw-track-intro__title">{{ $introTitle }}</h2>
                <p class="lw-track-intro__lead">{{ $introLead }}</p>
                <ul class="lw-track-benefits">
                    @foreach($benefits as $index => $benefit)
                        <li class="lw-track-benefit">
                            <span class="lw-track-benefit__icon" aria-hidden="true">
                                @if($index === 0)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                    </svg>
                                @elseif($index === 1)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                    </svg>
                                @endif
                            </span>
                            <span class="lw-track-benefit__text">
                                <strong>{{ $benefit['title'] }}</strong>
                                <span>{{ $benefit['desc'] }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </aside>

            <div class="lw-contact-forms">
                <article class="lw-form-card lw-contact-form-card">
                    <header class="lw-track-split__head">
                        <h1 id="contact-form-title" class="lw-track-split__title">Pengaduan</h1>
                        <p class="lw-track-split__lead">{{ $formLead }}</p>
                    </header>

                    <div id="environment-requirements" class="lw-is-hidden">
                        <x-service-requirements :items="config('kelurahan.layanan_persyaratan.kontak_pengaduan', [])" />
                    </div>

                    @if($errors->any())
                        <div class="lw-alert lw-alert--error lw-track-split__alert" role="alert">
                            <ul class="list-none p-0 m-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" id="contact-form" class="lw-track-split__form" enctype="multipart/form-data">
                        @csrf
                        <p class="lw-form-hint lw-mb-0">Field bertanda <span class="lw-form-label-required">*</span> wajib diisi.</p>

                        <div class="lw-form-grid lw-form-grid--2">
                            <div class="lw-form-field">
                                <label for="rt_profile_id" class="lw-form-label">RT terkait <span class="lw-form-label-required">*</span></label>
                                <select id="rt_profile_id" name="rt_profile_id" required class="lw-form-select">
                                    <option value="">— Pilih RT —</option>
                                    @foreach($rtProfiles as $rt)
                                        <option value="{{ $rt->id }}" @selected(old('rt_profile_id') == $rt->id)>
                                            {{ $rt->displayName() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('rt_profile_id')<p class="lw-form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="lw-form-field">
                                <label for="category" class="lw-form-label">Jenis pesan <span class="lw-form-label-required">*</span></label>
                                <select id="category" name="category" required class="lw-form-select" data-category-select>
                                    <option value="">— Pilih jenis —</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" @selected(old('category', request('category')) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category')<p class="lw-form-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="lw-form-field">
                                <label for="reporter_name" class="lw-form-label">Nama lengkap <span class="lw-form-label-required">*</span></label>
                                <input id="reporter_name" name="reporter_name" type="text" required maxlength="120"
                                    value="{{ old('reporter_name') }}" class="lw-form-input">
                            </div>
                            <div class="lw-form-field">
                                <label for="phone" class="lw-form-label">Nomor HP/ WhatsApp <span class="lw-form-label-required">*</span></label>
                                <x-phone-input id="phone" name="phone" :value="old('phone')" class="lw-form-input" required />
                            </div>
                        </div>

                        <div id="environment-fields" class="lw-is-hidden">
                            <div class="lw-form-grid lw-form-grid--2">
                                <div class="lw-form-field">
                                    <label for="incident_type" class="lw-form-label">Jenis masalah lingkungan <span class="lw-form-label-required">*</span></label>
                                    <select id="incident_type" name="incident_type" class="lw-form-select">
                                        <option value="">— Pilih jenis —</option>
                                        @foreach($incidentTypes as $key => $label)
                                            <option value="{{ $key }}" @selected(old('incident_type') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('incident_type')<p class="lw-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="lw-form-field">
                                    <label for="nik" class="lw-form-label">NIK (16 digit, opsional)</label>
                                    <input id="nik" name="nik" type="text" maxlength="16" inputmode="numeric" pattern="\d{16}"
                                        value="{{ old('nik') }}" class="lw-form-input">
                                    @error('nik')<p class="lw-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="lw-form-field lw-form-field--span2">
                                    <label for="incident_location" class="lw-form-label">Lokasi kejadian <span class="lw-form-label-required">*</span></label>
                                    <input id="incident_location" name="incident_location" type="text" maxlength="200"
                                        value="{{ old('incident_location') }}" class="lw-form-input" placeholder="Contoh: Jalan Merpati RT 05, dekat pos kamling">
                                    @error('incident_location')<p class="lw-form-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="lw-form-field lw-form-field--span2">
                                    <label for="photo" class="lw-form-label">Foto bukti (opsional)</label>
                                    <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="lw-form-input lw-form-file">
                                    @error('photo')<p class="lw-form-error">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="lw-form-field">
                            <label for="message" class="lw-form-label">Uraian <span class="lw-form-label-required">*</span></label>
                            <textarea id="message" name="message" rows="5" required maxlength="2000" class="lw-form-textarea" placeholder="Jelaskan kendala atau pertanyaan Anda secara jelas">{{ old('message') }}</textarea>
                            @error('message')<p class="lw-form-error">{{ $message }}</p>@enderror
                        </div>

                        <label class="lw-form-check">
                            <input type="checkbox" name="declaration" value="1" required @checked(old('declaration'))>
                            <span>Saya menyatakan data laporan ini benar dan bukan spam.</span>
                        </label>

                        <button type="submit" class="lw-track-split__submit">Kirim pesan</button>
                    </form>
                </article>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const select = document.querySelector('[data-category-select][name="category"]');
    const envWrap = document.getElementById('environment-fields');
    const envReq = document.getElementById('environment-requirements');
    const incidentType = document.getElementById('incident_type');
    const incidentLocation = document.getElementById('incident_location');
    if (!select) return;

    function toggle() {
        const isEnv = select.value === 'pengaduan_lingkungan';
        if (envWrap) envWrap.classList.toggle('lw-is-hidden', !isEnv);
        if (envReq) envReq.classList.toggle('lw-is-hidden', !isEnv);
        if (incidentType) incidentType.required = isEnv;
        if (incidentLocation) incidentLocation.required = isEnv;
    }

    select.addEventListener('change', toggle);
    toggle();
})();
</script>
@endpush
