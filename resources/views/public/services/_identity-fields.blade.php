@props(['rtProfiles', 'resident' => null])

<div class="lw-form-grid lw-form-grid--labeled">
    <div class="lw-form-field lw-form-field--span2">
        <label for="nik" class="lw-form-label">NIK (16 digit) <span class="lw-form-label-required">*</span></label>
        <input id="nik" name="nik" type="text" inputmode="numeric" maxlength="16" required
            value="{{ old('nik', $resident?->nik) }}" class="lw-form-input"
            @if($resident) readonly @endif>
        @if($resident)
        <p class="lw-form-hint lw-form-hint--accent">Data ditemukan — nama terisi otomatis.</p>
        @endif
        @error('nik')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field lw-form-field--span2">
        <label for="name" class="lw-form-label">Nama lengkap <span class="lw-form-label-required">*</span></label>
        <input id="name" name="name" type="text" required
            value="{{ old('name', $resident?->name) }}" class="lw-form-input"
            @if($resident) readonly @endif>
        @error('name')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field">
        <label for="phone" class="lw-form-label">Nomor HP/ WhatsApp <span class="lw-form-label-required">*</span></label>
        <x-phone-input id="phone" name="phone" :value="old('phone', $resident?->phone)" class="lw-form-input" required />
        @error('phone')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field">
        <label for="rt_profile_id" class="lw-form-label">RT <span class="lw-form-label-required">*</span></label>
        <select id="rt_profile_id" name="rt_profile_id" required class="lw-form-select"
            @if($resident?->household?->rt_profile_id) disabled @endif>
            <option value="">— Pilih RT —</option>
            @foreach($rtProfiles as $rt)
            <option value="{{ $rt->id }}"
                @selected(old('rt_profile_id', $resident?->household?->rt_profile_id) == $rt->id)>
                {{ $rt->displayName() }} — {{ $rt->kelurahan }}
            </option>
            @endforeach
        </select>
        @if($resident?->household?->rt_profile_id)
        <input type="hidden" name="rt_profile_id" value="{{ $resident->household->rt_profile_id }}">
        @endif
        @error('rt_profile_id')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field">
        <label for="house_number" class="lw-form-label">No. rumah</label>
        <input id="house_number" name="house_number" type="text"
            value="{{ old('house_number', $resident?->household?->house_number) }}" class="lw-form-input">
    </div>
    <div class="lw-form-field">
        <label for="birth_place" class="lw-form-label">Tempat lahir <span class="lw-form-label-required">*</span></label>
        <input id="birth_place" name="birth_place" type="text" required
            value="{{ old('birth_place', $resident?->birth_place) }}" class="lw-form-input">
        @error('birth_place')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field">
        <label for="birth_date" class="lw-form-label">Tanggal lahir <span class="lw-form-label-required">*</span></label>
        <input id="birth_date" name="birth_date" type="date" required
            value="{{ old('birth_date', $resident?->birth_date?->format('Y-m-d')) }}" class="lw-form-input">
        @error('birth_date')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field">
        <label for="gender" class="lw-form-label">Jenis kelamin <span class="lw-form-label-required">*</span></label>
        <select id="gender" name="gender" required class="lw-form-select">
            <option value="">— Pilih —</option>
            @foreach(['Laki-laki', 'Perempuan'] as $g)
            <option value="{{ $g }}" @selected(old('gender', $resident?->gender) === $g)>{{ $g }}</option>
            @endforeach
        </select>
        @error('gender')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field lw-form-field--span2">
        <label for="address" class="lw-form-label">Alamat tempat tinggal <span class="lw-form-label-required">*</span></label>
        <textarea id="address" name="address" rows="2" required class="lw-form-textarea">{{ old('address', $resident?->household?->address) }}</textarea>
        <p class="lw-form-hint">Isi sesuai alamat pada Kartu Keluarga atau KTP/KIA.</p>
        @error('address')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field lw-form-field--span2">
        @include('partials.whatsapp-notify-locked', [
            'label' => 'Terima notifikasi status permohonan via WhatsApp',
        ])
    </div>
</div>
