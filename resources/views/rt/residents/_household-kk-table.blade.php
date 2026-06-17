@php
    use App\Support\HouseholdHousingOptions;

    /** @var \App\Models\Household|null $household */
    $isEdit = ($mode ?? 'show') === 'edit';
    $resident = $resident ?? null;

    if ($isEdit) {
        $statusRumah = HouseholdHousingOptions::normalizeStatus(old('status_rumah_tinggal', $household?->status_rumah_tinggal));
        $legacyStatus = old('status_rumah_tinggal', $household?->status_rumah_tinggal);
        $hasLegacyStatus = filled($legacyStatus) && ! array_key_exists($statusRumah ?? '', HouseholdHousingOptions::statusOptions());
        $kondisi = old('kondisi_rumah_milik', $household?->kondisi_rumah_milik);
        $showKondisi = HouseholdHousingOptions::requiresKondisiRumahMilik($statusRumah ?? $legacyStatus);
    }
@endphp

@if($household)
    <tr class="lw-rt-resident-detail-section">
        <th colspan="2">Kartu keluarga</th>
    </tr>
    <tr>
        <th scope="row">No. KK</th>
        <td>
            @if($isEdit)
                <input name="family_card_number" value="{{ old('family_card_number', $household->family_card_number) }}" maxlength="16" inputmode="numeric" pattern="\d{16}" required>
                @error('family_card_number')<p class="lw-form-error">{{ $message }}</p>@enderror
            @else
                {{ $household->family_card_number }}
            @endif
        </td>
    </tr>
    <tr>
        <th scope="row">Kepala keluarga</th>
        <td>
            @if($isEdit && $resident)
                <span>{{ $household->headResident?->name ?? '—' }}</span>
                <label class="lw-rt-edit-check lw-ml-2">
                    <input type="checkbox" name="is_head_of_family" id="is_head_of_family" value="1" @checked(old('is_head_of_family', $resident->is_head_of_family))>
                    Tandai warga ini sebagai kepala keluarga
                </label>
            @else
                {{ $household->headResident?->name ?? '—' }}
            @endif
        </td>
    </tr>
    <tr>
        <th scope="row">Alamat tempat tinggal</th>
        <td>
            @if($isEdit)
                <textarea name="address" rows="2" required>{{ old('address', $household->address) }}</textarea>
                @error('address')<p class="lw-form-error">{{ $message }}</p>@enderror
            @else
                {{ $resident ? $resident->fullAddress() : ($household->address ?: '—') }}
            @endif
        </td>
    </tr>
    <tr>
        <th scope="row">No. rumah</th>
        <td>
            @if($isEdit)
                <input name="house_number" value="{{ old('house_number', $household->house_number) }}" maxlength="20">
            @else
                {{ $household->house_number ?: '—' }}
            @endif
        </td>
    </tr>
    <tr>
        <th scope="row">Status rumah</th>
        <td>
            @if($isEdit)
                <select name="status_rumah_tinggal" id="status_rumah_tinggal" data-status-rumah-input required>
                    <option value="">— Pilih —</option>
                    @foreach (HouseholdHousingOptions::statusOptions() as $value => $label)
                        <option value="{{ $value }}" @selected($statusRumah === $value)>{{ $label }}</option>
                    @endforeach
                    @if($hasLegacyStatus)
                        <option value="{{ $legacyStatus }}" @selected(true)>{{ $legacyStatus }} (data lama)</option>
                    @endif
                </select>
                @error('status_rumah_tinggal')<p class="lw-form-error">{{ $message }}</p>@enderror
            @else
                {{ HouseholdHousingOptions::statusLabel($household->status_rumah_tinggal) }}
            @endif
        </td>
    </tr>
    @if(! $isEdit)
    <tr>
        <th scope="row">Suku</th>
        <td>{{ $household->suku ?: '—' }}</td>
    </tr>
    @endif
    <tr @if($isEdit) id="row-kondisi-rumah" @class(['is-hidden' => ! $showKondisi]) @endif>
        <th scope="row">Kondisi rumah</th>
        <td>
            @if($isEdit)
                <select name="kondisi_rumah_milik" id="kondisi_rumah_milik" data-kondisi-input @if($showKondisi) required @endif>
                    <option value="">— Pilih —</option>
                    @foreach (HouseholdHousingOptions::kondisiOptions() as $value => $label)
                        <option value="{{ $value }}" @selected($kondisi === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="lw-panel-field-hint">Wajib diisi jika status rumah tinggal adalah milik sendiri.</p>
                @error('kondisi_rumah_milik')<p class="lw-form-error">{{ $message }}</p>@enderror
            @else
                {{ HouseholdHousingOptions::kondisiLabel($household->kondisi_rumah_milik) }}
            @endif
        </td>
    </tr>
@endif

@once
@push('scripts')
<script>
(function () {
    const milikSendiriValue = @json(HouseholdHousingOptions::STATUS_MILIK_SENDIRI);

    function syncKondisiRequired() {
        const statusInput = document.getElementById('status_rumah_tinggal');
        const kondisiRow = document.getElementById('row-kondisi-rumah');
        const kondisiSelect = document.getElementById('kondisi_rumah_milik');
        if (!statusInput || !kondisiRow || !kondisiSelect) return;

        const needs = statusInput.value === milikSendiriValue;
        kondisiRow.classList.toggle('is-hidden', !needs);
        kondisiSelect.required = needs;
        if (!needs) kondisiSelect.value = '';
    }

    document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'status_rumah_tinggal') {
            syncKondisiRequired();
        }
    });
    document.addEventListener('DOMContentLoaded', syncKondisiRequired);
})();
</script>
@endpush
@endonce
