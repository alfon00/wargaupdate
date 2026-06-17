@props([
    'prefix' => '',
    'values' => [],
    'required' => true,
    'headRequiredFields' => [],
])

@php
    $demo = config('kelurahan.resident_demographics', []);
    $p = $prefix !== '' ? $prefix : '';
    $name = fn (string $key) => $p !== '' ? "{$p}[{$key}]" : $key;
    $id = fn (string $key) => ($prefix !== '' ? str_replace(['[', ']'], ['_', ''], $prefix).'_' : '').$key;
    $val = fn (string $key) => old($name($key), $values[$key] ?? '');
    $fieldRequired = fn (string $key) => $required || in_array($key, $headRequiredFields, true);
    $occupationVal = $val('occupation');
    $occupations = $demo['occupations'] ?? [];
    $occupationLegacy = filled($occupationVal) && ! in_array($occupationVal, $occupations, true);
@endphp

<div class="lw-form-grid lw-form-grid--2 lw-demographics-fields">
    <div class="lw-form-field">
        <label for="{{ $id('occupation') }}" class="lw-form-label">Pekerjaan @if($fieldRequired('occupation'))<span class="lw-form-label-required">*</span>@endif</label>
        <select id="{{ $id('occupation') }}" name="{{ $name('occupation') }}" class="lw-form-select" @if($fieldRequired('occupation')) required @endif>
            <option value="">— Pilih —</option>
            @if($occupationLegacy)
                <option value="{{ $occupationVal }}" selected>{{ $occupationVal }}</option>
            @endif
            @foreach($occupations as $occ)
                <option value="{{ $occ }}" @selected(! $occupationLegacy && $occupationVal === $occ)>{{ $occ }}</option>
            @endforeach
        </select>
        @error($name('occupation'))<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field">
        <label for="{{ $id('education') }}" class="lw-form-label">Pendidikan @if($fieldRequired('education'))<span class="lw-form-label-required">*</span>@endif</label>
        <select id="{{ $id('education') }}" name="{{ $name('education') }}" class="lw-form-select" @if($fieldRequired('education')) required @endif>
            <option value="">— Pilih —</option>
            @foreach($demo['education_levels'] ?? [] as $level)
                <option value="{{ $level }}" @selected($val('education') === $level)>{{ $level }}</option>
            @endforeach
        </select>
        @error($name('education'))<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field">
        <label for="{{ $id('religion') }}" class="lw-form-label">Agama @if($fieldRequired('religion'))<span class="lw-form-label-required">*</span>@endif</label>
        <select id="{{ $id('religion') }}" name="{{ $name('religion') }}" class="lw-form-select" @if($fieldRequired('religion')) required @endif>
            <option value="">— Pilih —</option>
            @foreach($demo['religions'] ?? [] as $rel)
                <option value="{{ $rel }}" @selected($val('religion') === $rel)>{{ $rel }}</option>
            @endforeach
        </select>
        @error($name('religion'))<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field">
        <label for="{{ $id('marital_status') }}" class="lw-form-label">Status perkawinan @if($fieldRequired('marital_status'))<span class="lw-form-label-required">*</span>@endif</label>
        <select id="{{ $id('marital_status') }}" name="{{ $name('marital_status') }}" class="lw-form-select" @if($fieldRequired('marital_status')) required @endif>
            <option value="">— Pilih —</option>
            @foreach($demo['marital_statuses'] ?? [] as $status)
                <option value="{{ $status }}" @selected($val('marital_status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error($name('marital_status'))<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="lw-form-field">
        <label for="{{ $id('citizenship') }}" class="lw-form-label">Kewarganegaraan @if($fieldRequired('citizenship'))<span class="lw-form-label-required">*</span>@endif</label>
        <select id="{{ $id('citizenship') }}" name="{{ $name('citizenship') }}" class="lw-form-select" @if($fieldRequired('citizenship')) required @endif>
            @foreach($demo['citizenships'] ?? ['WNI'] as $cit)
                <option value="{{ $cit }}" @selected($val('citizenship', 'WNI') === $cit)>{{ $cit }}</option>
            @endforeach
        </select>
        @error($name('citizenship'))<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
</div>
