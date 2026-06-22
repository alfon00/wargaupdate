@extends('layouts.panel')

@section('title', $resident->exists ? 'Edit Warga — '.$resident->name : 'Tambah Warga')

@section('content')
<div class="lw-rt-page">
@if($resident->exists)
@php
    $household = $resident->household;
    $showQuery = array_merge(['resident' => $resident], $listQuery ?? []);
@endphp
<div class="lw-rt-resident-edit-page">
@include('rt.partials.page-head', [
    'eyebrow' => 'Edit warga',
    'title' => $resident->name,
    'lead' => ($resident->domicile_status?->label() ?? '—').' · '.($household?->rtProfile?->displayName() ?? '—'),
])

<article class="lw-panel-card lw-panel-card--full">
    <h2 class="lw-panel-card-title">Edit warga</h2>

@if(! empty($pendataanReturn))
<p class="lw-mb-3">
    <a href="{{ route('rt.pendataan.show', $pendataanReturn) }}" class="lw-panel-page-back">← Kembali ke verifikasi pendataan</a>
</p>
@endif

<form method="POST" action="{{ route('rt.residents.update', $resident) }}" enctype="multipart/form-data" class="lw-panel-form lw-panel-form--wide lw-panel-form--in-card">
    @csrf
    @method('PUT')
    @if(! empty($pendataanReturn))
        <input type="hidden" name="return" value="pendataan">
        <input type="hidden" name="pendataan_head" value="{{ $pendataanReturn }}">
        <input type="hidden" name="household_id" value="{{ $household?->id }}">
    @endif

    @include('rt.residents._edit-profile-table')

    @if($household)
        @include('rt.residents._edit-pendataan-documents', [
            'household' => $household,
            'resident' => $resident,
            'head' => $resident->is_head_of_family ? $resident : $household->headResident,
            'faceReadiness' => $faceReadiness ?? null,
        ])
    @endif

    <div class="lw-panel-form-actions">
        <button type="submit" class="lw-panel-btn">Simpan</button>
        @if(! empty($pendataanReturn))
            <a href="{{ route('rt.pendataan.show', $pendataanReturn) }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
        @else
            <a href="{{ route('rt.residents.show', $showQuery) }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
        @endif
    </div>
    @php
        $lastUpdatedAt = collect([$resident->updated_at, $household?->updated_at])->filter()->max();
    @endphp
    @if($lastUpdatedAt)
        <p class="lw-rt-resident-last-updated">
            Terakhir diperbarui: {{ $lastUpdatedAt->timezone('Asia/Jayapura')->format('d/m/Y H:i') }}
        </p>
    @endif
</form>
</article>

@include('rt.partials.delete-danger-zone', [
    'description' => 'Mengajukan penghapusan permanen ke admin kelurahan. Memerlukan tanda tangan Ketua RT dan persetujuan admin.',
    'label' => 'Hapus warga permanen',
    'confirm' => 'Ajukan hapus permanen data '.$resident->name.'? Data akan dihapus setelah admin kelurahan menyetujui.',
    'action' => route('rt.residents.destroy', $resident),
    'hidden' => [],
    'enabled' => $resident->canBePermanentlyDeleted(),
    'disabledTitle' => $resident->deletionBlockReason(),
])
</div>
@else
@php
    $lockedHousehold = $preselectedHousehold ?? null;
    $selectedHouseholdId = old('household_id', $preselectedHouseholdId ?? null);
    if (! $lockedHousehold && $selectedHouseholdId) {
        $lockedHousehold = $households->firstWhere('id', (int) $selectedHouseholdId);
    }
    $relationshipOptions = ['Kepala Keluarga', 'Istri', 'Anak', 'Orang Tua', 'Anggota Keluarga', 'Lainnya'];
    $currentRelationship = old('relationship_to_head', '');
    $relationshipPreset = in_array($currentRelationship, $relationshipOptions, true) ? $currentRelationship : (filled($currentRelationship) ? 'Lainnya' : '');
    $relationshipCustom = $relationshipPreset === 'Lainnya' ? $currentRelationship : '';
    $backUrl = $backResident ?? null
        ? route('rt.residents.show', array_merge(['resident' => $backResident], $listQuery ?? []))
        : route('rt.data-warga.index', array_filter([
            'household' => $lockedHousehold?->id ?? $selectedHouseholdId,
            'filter' => $listQuery['filter'] ?? null,
            'kategori' => $listQuery['kategori'] ?? null,
            'q' => $listQuery['q'] ?? null,
        ], fn ($value) => filled($value)));
    $createTitle = $lockedHousehold
        ? 'Tambah anggota — '.$lockedHousehold->family_card_number
        : 'Tambah Warga';
@endphp
@include('rt.partials.page-head', [
    'eyebrow' => 'Tambah warga',
    'title' => $createTitle,
    'lead' => $lockedHousehold
        ? ($lockedHousehold->address ?: 'Tambahkan anggota ke kartu keluarga ini.')
        : 'Kelola data warga di wilayah RT Anda.',
])

<p class="lw-mb-4">
    <a href="{{ $backUrl }}" class="lw-panel-page-back">← Kembali</a>
</p>

<article class="lw-panel-card lw-panel-card--full">
<form method="POST" action="{{ route('rt.residents.store') }}" class="lw-panel-form lw-panel-form--wide lw-panel-form--labeled lw-panel-form--in-card">
    @csrf
    @foreach($listQuery ?? [] as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Identitas warga</legend>
        @if($lockedHousehold)
            <input type="hidden" name="household_id" value="{{ $lockedHousehold->id }}">
            <div class="lw-panel-field">
                <label>KK / Rumah Tangga</label>
                <p class="lw-mb-0"><strong>{{ $lockedHousehold->family_card_number }}</strong> — {{ $lockedHousehold->rtProfile?->displayName() }}</p>
                @if($lockedHousehold->address)
                    <p class="lw-panel-field-hint lw-mb-0">{{ $lockedHousehold->address }}</p>
                @endif
            </div>
        @else
            <div class="lw-panel-field">
                <label>KK / Rumah Tangga <span class="lw-form-label-required">*</span></label>
                <select name="household_id" required>
                    @foreach($households as $h)
                    <option value="{{ $h->id }}" @selected(old('household_id', $selectedHouseholdId) == $h->id)>{{ $h->family_card_number }} — {{ $h->rtProfile?->displayName() }}</option>
                    @endforeach
                </select>
                <p class="lw-panel-field-hint">
                    Belum punya KK?
                    <a href="{{ route('rt.data-warga.create') }}" class="lw-panel-link">+ Daftar KK &amp; warga</a>
                </p>
            </div>
        @endif
        <div class="lw-panel-field"><label>NIK</label><input name="nik" value="{{ old('nik', $resident->nik) }}" maxlength="16"></div>
        <div class="lw-panel-field"><label>Nama <span class="lw-form-label-required">*</span></label><input name="name" value="{{ old('name', $resident->name) }}" required></div>
        <div class="lw-panel-field"><label>Tempat Lahir</label><input name="birth_place" value="{{ old('birth_place', $resident->birth_place) }}"></div>
        <div class="lw-panel-field"><label>Tanggal Lahir</label><input type="date" name="birth_date" value="{{ old('birth_date', $resident->birth_date?->format('Y-m-d')) }}"></div>
        <div class="lw-panel-field">
            <label>Jenis kelamin</label>
            <select name="gender">
                <option value="">—</option>
                @foreach(['Laki-laki', 'Perempuan'] as $g)
                <option value="{{ $g }}" @selected(old('gender', $resident->gender) === $g)>{{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div class="lw-panel-field">
            <label>Hubungan dalam KK</label>
            <select name="relationship_preset" id="relationship_preset" class="lw-mb-1">
                <option value="">— Pilih —</option>
                @foreach($relationshipOptions as $opt)
                    <option value="{{ $opt }}" @selected($relationshipPreset === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
            <input type="text" name="relationship_to_head" id="relationship_to_head" maxlength="30"
                value="{{ old('relationship_to_head', $relationshipPreset === 'Lainnya' ? $relationshipCustom : ($relationshipPreset ?: $currentRelationship)) }}"
                placeholder="Isi hubungan dalam KK"
                @if($relationshipPreset !== 'Lainnya' && filled($relationshipPreset)) readonly @endif>
            @error('relationship_to_head')<p class="lw-form-error">{{ $message }}</p>@enderror
        </div>
    </fieldset>

    <fieldset class="lw-panel-form-fieldset" id="demographics-fieldset">
        <legend class="lw-panel-form-legend">Demografi</legend>
        <p class="lw-panel-field-hint lw-mb-3 is-hidden" id="head-recap-hint">
            Agama dan pekerjaan wajib diisi jika warga ditandai sebagai kepala keluarga (untuk rekap kelurahan).
        </p>
        @include('public.services._resident-demographics-fields', [
            'prefix' => '',
            'values' => [
                'occupation' => old('occupation', $resident->occupation),
                'education' => old('education', $resident->education),
                'religion' => old('religion', $resident->religion),
                'marital_status' => old('marital_status', $resident->marital_status),
                'citizenship' => old('citizenship', $resident->citizenship ?: 'WNI'),
            ],
            'required' => false,
            'headRequiredFields' => ['occupation', 'religion'],
        ])
    </fieldset>

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Kontak &amp; notifikasi</legend>
        <div class="lw-panel-field">
            <label>Telepon</label>
            <x-phone-input name="phone" :value="old('phone', $resident->phone)" />
            <p class="lw-panel-field-hint">Nomor untuk verifikasi layanan portal. Perbarui jika kartu lama tidak aktif.</p>
        </div>
        <label class="lw-panel-field lw-panel-field--inline">
            <input type="checkbox" name="is_head_of_family" id="is_head_of_family" value="1" @checked(old('is_head_of_family', $resident->is_head_of_family))> Kepala keluarga
        </label>
        <div class="lw-panel-field lw-panel-field--inline">
            @include('partials.whatsapp-notify-locked', [
                'label' => 'Notifikasi WhatsApp',
                'checkClass' => 'lw-panel-check',
            ])
        </div>
    </fieldset>

    <div class="lw-panel-form-actions">
        <button type="submit" class="lw-panel-btn">Simpan</button>
        <a href="{{ $backUrl }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
    </div>
</form>
</article>
@endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var headCheckbox = document.getElementById('is_head_of_family');
    var hint = document.getElementById('head-recap-hint');
    var occupation = document.getElementById('occupation');
    var religion = document.getElementById('religion');

    function syncHeadRequired() {
        var isHead = headCheckbox && headCheckbox.checked;
        if (hint) {
            hint.classList.toggle('is-hidden', ! isHead);
        }
        [occupation, religion].forEach(function (el) {
            if (! el) return;
            el.required = isHead;
        });
    }

    if (headCheckbox) {
        headCheckbox.addEventListener('change', syncHeadRequired);
        syncHeadRequired();
    }

    var relationshipPreset = document.getElementById('relationship_preset');
    var relationshipInput = document.getElementById('relationship_to_head');

    function syncRelationshipField() {
        if (! relationshipPreset || ! relationshipInput) return;
        var preset = relationshipPreset.value;
        if (preset === 'Lainnya') {
            relationshipInput.readOnly = false;
            if (! relationshipInput.value || relationshipPreset.dataset.last !== 'Lainnya') {
                relationshipInput.value = '';
            }
            relationshipInput.focus();
        } else if (preset) {
            relationshipInput.value = preset;
            relationshipInput.readOnly = true;
        } else {
            relationshipInput.readOnly = false;
        }
        relationshipPreset.dataset.last = preset;
    }

    if (relationshipPreset) {
        relationshipPreset.addEventListener('change', syncRelationshipField);
        syncRelationshipField();
    }
});
</script>
@endpush
