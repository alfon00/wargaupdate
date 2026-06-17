@props([
    'name',
    'id' => null,
    'value' => '',
    'required' => false,
    'class' => '',
    'placeholder' => '08xxxxxxxxxx',
    'autocomplete' => 'tel',
])

<input
    type="tel"
    name="{{ $name }}"
    @if($id) id="{{ $id }}" @endif
    value="{{ $value }}"
    inputmode="numeric"
    maxlength="12"
    minlength="11"
    pattern="(0\d{10,11}|62\d{9,10})"
    placeholder="{{ $placeholder }}"
    autocomplete="{{ $autocomplete }}"
    @if($required) required @endif
    {{ $attributes->merge(['class' => $class]) }}
>
