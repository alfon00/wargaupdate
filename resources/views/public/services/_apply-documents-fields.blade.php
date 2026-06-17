@php
    $requiredFields = $service->required_fields ?? [];
@endphp

@if(! empty($requiredFields))
<fieldset class="lw-form-fieldset">
    <legend class="lw-form-legend">Lampiran berkas</legend>
    <p class="lw-form-hint lw-mb-2">Unggah scan atau foto berkas asli pemohon. Pengurus RT memakai lampiran ini untuk memeriksa kelengkapan permohonan.</p>
    <ul class="lw-form-hint lw-mb-4">
        <li><strong>Kartu Keluarga (KK)</strong> — halaman depan KK yang masih berlaku</li>
        <li><strong>KTP atau KIA</strong> — identitas pemohon yang sama dengan data di atas</li>
    </ul>
    <p class="lw-form-hint lw-mb-4">Format PDF, JPG, atau PNG. Maks. 5 MB per berkas. Pastikan nama dan NIK terbaca jelas.</p>

    @error('documents')<p class="lw-form-error">{{ $message }}</p>@enderror

    <div class="lw-form-stack">
        @foreach($requiredFields as $index => $field)
            @php
                $label = match ($field) {
                    'KK' => 'Kartu Keluarga (KK)',
                    'KTP' => 'KTP atau KIA',
                    default => $field,
                };
                $inputId = 'document_'.$index;
            @endphp
            <div class="lw-form-field">
                <label for="{{ $inputId }}" class="lw-form-label">
                    {{ $label }} <span class="lw-form-label-required">*</span>
                </label>
                <input id="{{ $inputId }}" name="documents[{{ $index }}]" type="file"
                    class="lw-form-input lw-form-file" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('documents.'.$index)<p class="lw-form-error">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
</fieldset>
@endif
