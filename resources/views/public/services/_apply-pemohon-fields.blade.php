@props(['rtProfiles', 'resident' => null])

<div class="lw-form-grid lw-form-grid--labeled">
    <div class="lw-form-field lw-form-field--span2">
        <label for="nik" class="lw-form-label">NIK (16 digit) <span class="lw-form-label-required">*</span></label>
        <input id="nik" name="nik" type="text" inputmode="numeric" maxlength="16" required
            value="{{ old('nik', $resident?->nik) }}" class="lw-form-input" readonly>
        <p class="lw-form-hint lw-form-hint--accent">Identitas dari verifikasi — tidak dapat diubah.</p>
        @error('nik')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field lw-form-field--span2">
        <label for="name" class="lw-form-label">Nama lengkap <span class="lw-form-label-required">*</span></label>
        <input id="name" name="name" type="text" required
            value="{{ old('name', $resident?->name) }}" class="lw-form-input" readonly>
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
    <div class="lw-form-field lw-form-field--span2">
        @include('partials.whatsapp-notify-locked', [
            'label' => 'Terima notifikasi status permohonan via WhatsApp',
        ])
    </div>
</div>
