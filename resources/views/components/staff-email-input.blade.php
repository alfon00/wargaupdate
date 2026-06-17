@props([
    'name' => 'email_local',
    'id' => 'email_local',
    'value' => '',
    'required' => true,
    'class' => '',
    'placeholder' => 'ketua.rt008',
    'autocomplete' => 'username',
])

<div class="lw-staff-email-input">
    <input
        type="text"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        inputmode="email"
        autocapitalize="none"
        spellcheck="false"
        placeholder="{{ $placeholder }}"
        autocomplete="{{ $autocomplete }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'lw-staff-email-input__local '.$class]) }}
    >
    <span class="lw-staff-email-input__suffix" aria-hidden="true">{{ \App\Support\StaffEmail::suffix() }}</span>
</div>
