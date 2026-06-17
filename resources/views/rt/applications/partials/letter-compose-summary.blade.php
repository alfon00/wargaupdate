@php
    use App\Support\LetterFieldSchema;

    $profileIncomplete = $profileIncomplete ?? false;
    $missingProfileLabels = $missingProfileLabels ?? [];
    $autoKeys = array_values(array_filter(
        LetterFieldSchema::autoFilledKeys(),
        static fn (string $key): bool => $key !== 'keperluan',
    ));
    $fieldByKey = collect($fieldSchema)->keyBy('key');
    $composeFieldOrder = [
        'nama',
        'nik',
        'ttl',
        'pekerjaan',
        'agama',
        'status_perkawinan',
        'kewarganegaraan',
        'alamat',
    ];
    $orderedKeys = array_values(array_unique(array_merge(
        array_intersect($composeFieldOrder, $autoKeys),
        array_diff($autoKeys, $composeFieldOrder),
    )));
@endphp

@if($profileIncomplete)
<div class="lw-alert lw-alert--warn lw-mb-4" role="alert">
    <p class="m-0"><strong>Data warga belum lengkap untuk surat.</strong>
            Minta warga melengkapi via <a href="{{ route('services.pendataan-ulang') }}" class="lw-panel-link" target="_blank" rel="noopener">Pendataan ulang</a>
        atau edit data warga di panel RT.</p>
    @if(count($missingProfileLabels) > 0)
    <p class="lw-panel-card-note m-0 mt-2">Kosong: {{ implode(', ', array_values($missingProfileLabels)) }}</p>
    @endif
</div>
@endif

<div class="lw-letter-compose-applicant-toolbar">
    <button type="button" id="letter-applicant-edit-toggle"
        class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm"
        aria-pressed="false">
        Ubah data pemohon
    </button>
</div>

<p class="lw-panel-card-note lw-letter-compose-fields-note m-0">
    Data di bawah terisi otomatis dari data warga permohonan. Klik <strong>Ubah data pemohon</strong> untuk menyesuaikan manual.
</p>

<div class="lw-letter-compose-fields">
    @foreach($orderedKeys as $key)
        @php
            $field = $fieldByKey->get($key);
            if (! $field) {
                continue;
            }
            $value = old('fields.'.$key, $fieldValues[$key] ?? '');
            $isTextarea = ($field['type'] ?? 'text') === 'textarea';
            $required = (bool) ($field['required'] ?? false);
        @endphp
        <div class="lw-panel-field">
            <label for="letter-field-{{ $key }}" class="lw-panel-field-label">
                {{ $field['label'] }}
                @if($required)
                    <span class="lw-form-label-required">*</span>
                @endif
            </label>
            @if($isTextarea)
                <textarea id="letter-field-{{ $key }}" name="fields[{{ $key }}]" rows="3"
                    class="lw-letter-compose-field-input lw-panel-field-input"
                    data-letter-field="1" data-applicant-field="1" readonly
                    @if($required) data-required="1" required @endif>{{ $value }}</textarea>
            @else
                <input type="text" id="letter-field-{{ $key }}" name="fields[{{ $key }}]" value="{{ $value }}"
                    maxlength="2000"
                    class="lw-letter-compose-field-input lw-panel-field-input"
                    data-letter-field="1" data-applicant-field="1" readonly
                    @if($required) data-required="1" required @endif>
            @endif
            @error('fields.'.$key)
                <p class="lw-form-error">{{ $message }}</p>
            @enderror
        </div>
    @endforeach
</div>
