@php
    $key = $field['key'];
    $value = old('fields.'.$key, $fieldValues[$key] ?? '');
    $isTextarea = ($field['type'] ?? 'text') === 'textarea';
    $spanClass = $isTextarea ? 'lw-panel-field lw-form-field--span2' : 'lw-panel-field';
@endphp
<div class="{{ $spanClass }}">
    <label for="field_{{ $key }}">
        {{ $field['label'] }}
        @if($field['required'])<span class="lw-form-label-required">*</span>@endif
    </label>
    @if($isTextarea)
        <textarea id="field_{{ $key }}" name="fields[{{ $key }}]" rows="3"
            @if($field['required']) required @endif>{{ $value }}</textarea>
    @else
        <input type="text" id="field_{{ $key }}" name="fields[{{ $key }}]" value="{{ $value }}"
            @if($field['required']) required @endif>
    @endif
    @error('fields.'.$key)
        <p class="lw-form-error">{{ $message }}</p>
    @enderror
</div>
