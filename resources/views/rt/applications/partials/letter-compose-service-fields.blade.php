@php
    use App\Support\LetterFieldSchema;

    $serviceCode = $application->serviceType->code;
    $serviceFields = LetterFieldSchema::serviceSpecificFields($serviceCode);
@endphp

@if(count($serviceFields) > 0)
<fieldset class="lw-form-fieldset lw-letter-fieldset lw-letter-compose-service-fields">
    <legend class="lw-form-legend">Data tambahan surat</legend>
    <p class="lw-panel-card-note m-0 mb-3">
        Lengkapi data khusus jenis surat ini. Field wajib harus diisi sebelum menerbitkan PDF.
    </p>
    <div class="lw-letter-compose-service-fields-grid">
        @foreach($serviceFields as $field)
            @php
                $key = $field['key'];
                $value = old('fields.'.$key, $fieldValues[$key] ?? '');
                $isTextarea = ($field['type'] ?? 'text') === 'textarea';
                $required = (bool) ($field['required'] ?? false);
            @endphp
            <div class="lw-panel-field">
                <label for="letter-field-{{ $key }}">
                    {{ $field['label'] }}
                    @if($required)
                        <span class="lw-form-label-required">*</span>
                    @endif
                </label>
                @if($isTextarea)
                    <textarea id="letter-field-{{ $key }}" name="fields[{{ $key }}]" rows="3"
                        class="lw-letter-compose-field-input"
                        data-letter-field="1"
                        @if($required) data-required="1" required @endif>{{ $value }}</textarea>
                @else
                    <input type="text" id="letter-field-{{ $key }}" name="fields[{{ $key }}]"
                        value="{{ $value }}" maxlength="2000"
                        class="lw-letter-compose-field-input"
                        data-letter-field="1"
                        @if($required) data-required="1" required @endif>
                @endif
                @error('fields.'.$key)
                    <p class="lw-form-error">{{ $message }}</p>
                @enderror
            </div>
        @endforeach
    </div>
</fieldset>
@endif
