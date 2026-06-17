@props([
    'household' => null,
    'required' => true,
    'context' => 'public',
])

@php
    use App\Support\HouseholdHousingOptions;

    $isPanel = $context === 'panel';
    $reqMark = $required ? '<span class="lw-form-label-required">*</span>' : '';
    $statusRumah = HouseholdHousingOptions::normalizeStatus(old('status_rumah_tinggal', $household?->status_rumah_tinggal));
    $legacyStatus = old('status_rumah_tinggal', $household?->status_rumah_tinggal);
    $hasLegacyStatus = filled($legacyStatus) && ! array_key_exists($statusRumah ?? '', HouseholdHousingOptions::statusOptions());
    $kondisi = old('kondisi_rumah_milik', $household?->kondisi_rumah_milik);
    $showKondisi = HouseholdHousingOptions::requiresKondisiRumahMilik($statusRumah ?? $legacyStatus);
    $wrapClass = $isPanel
        ? 'lw-household-recap-fields lw-household-recap-fields--panel'
        : 'lw-form-grid lw-form-grid--2 lw-household-recap-fields';
    $fieldClass = $isPanel ? 'lw-panel-field' : 'lw-form-field';
    $span2Class = $isPanel ? 'lw-panel-field lw-panel-field--span2' : 'lw-form-field lw-form-field--span2';
    $labelClass = $isPanel ? 'lw-panel-field-label' : 'lw-form-label';
    $inputClass = $isPanel ? 'lw-panel-field-input' : 'lw-form-input';
    $selectClass = $isPanel ? 'lw-panel-field-input' : 'lw-form-select';
    $hintClass = $isPanel ? 'lw-panel-field-hint' : 'lw-form-hint';
    $milikSendiriValue = HouseholdHousingOptions::STATUS_MILIK_SENDIRI;
@endphp

<div class="{{ $wrapClass }}" data-household-recap-fields data-milik-sendiri-value="{{ $milikSendiriValue }}">
    <div class="{{ $fieldClass }}">
        <label for="status_rumah_tinggal" class="{{ $labelClass }}">Status rumah tinggal {!! $reqMark !!}</label>
        <select id="status_rumah_tinggal" name="status_rumah_tinggal" class="{{ $selectClass }}"
            data-status-rumah-input @if($required) required @endif>
            <option value="">— Pilih —</option>
            @foreach (HouseholdHousingOptions::statusOptions() as $value => $label)
                <option value="{{ $value }}" @selected($statusRumah === $value)>{{ $label }}</option>
            @endforeach
            @if($hasLegacyStatus)
                <option value="{{ $legacyStatus }}" @selected(true)>{{ $legacyStatus }} (data lama)</option>
            @endif
        </select>
        @error('status_rumah_tinggal')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div class="{{ $fieldClass }}">
        <label for="suku" class="{{ $labelClass }}">Suku {!! $reqMark !!}</label>
        <input id="suku" name="suku" type="text" maxlength="100"
            value="{{ old('suku', $household?->suku) }}" class="{{ $inputClass }}" placeholder="Contoh: Amungme / Kamoro"
            @if($required) required @endif>
        @error('suku')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
    <div @class([$span2Class, 'lw-is-hidden' => ! $showKondisi]) id="field-kondisi-rumah" data-kondisi-field>
        <label for="kondisi_rumah_milik" class="{{ $labelClass }}">Kondisi rumah milik sendiri <span class="lw-form-label-required kondisi-req-mark" @if(! $showKondisi) hidden @endif>*</span></label>
        <select id="kondisi_rumah_milik" name="kondisi_rumah_milik" class="{{ $selectClass }}" data-kondisi-input
            @if($showKondisi && $required) required @endif
            @disabled(! $showKondisi)>
            <option value="">— Pilih —</option>
            @foreach (HouseholdHousingOptions::kondisiOptions() as $value => $label)
                <option value="{{ $value }}" @selected($kondisi === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="{{ $hintClass }}">Wajib diisi jika status rumah tinggal adalah milik sendiri.</p>
        @error('kondisi_rumah_milik')<p class="lw-form-error">{{ $message }}</p>@enderror
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    function syncKondisiRumah(scope) {
        const root = scope || document;
        root.querySelectorAll('[data-household-recap-fields]').forEach(function (wrap) {
            const statusInput = wrap.querySelector('[data-status-rumah-input]');
            const kondisiField = wrap.querySelector('[data-kondisi-field]');
            const kondisiInput = wrap.querySelector('[data-kondisi-input]');
            if (!statusInput || !kondisiField || !kondisiInput) return;

            const milikValue = wrap.dataset.milikSendiriValue || 'Milik sendiri';
            const isMilik = statusInput.value === milikValue;
            const reqMark = wrap.querySelector('.kondisi-req-mark');
            kondisiField.classList.toggle('lw-is-hidden', !isMilik);
            kondisiInput.required = isMilik;
            kondisiInput.disabled = !isMilik;
            if (reqMark) {
                reqMark.hidden = !isMilik;
            }
            if (!isMilik) kondisiInput.value = '';
        });
    }

    document.addEventListener('change', function (e) {
        if (e.target.matches('[data-status-rumah-input]')) {
            syncKondisiRumah(e.target.closest('form') || document);
        }
    });
    document.addEventListener('DOMContentLoaded', function () { syncKondisiRumah(); });
})();
</script>
@endpush
@endonce
