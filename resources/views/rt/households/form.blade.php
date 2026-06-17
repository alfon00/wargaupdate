@extends('layouts.panel')

@section('title', $household->exists ? 'Edit KK' : 'Tambah KK')

@section('content')
<div class="lw-rt-page lw-rt-household-edit-page">
@include('rt.partials.page-head', [
    'eyebrow' => $household->exists ? 'Edit kartu keluarga' : 'Tambah kartu keluarga',
    'title' => $household->exists ? $household->family_card_number : 'Kartu keluarga baru',
    'lead' => $rt->displayName().' — '.$rt->kelurahan,
])

<p class="lw-mb-4">
    @if(! empty($pendataanReturn))
        <a href="{{ route('rt.pendataan.show', $pendataanReturn) }}" class="lw-panel-page-back">← Kembali ke verifikasi pendataan</a>
    @else
        <a href="{{ route('rt.data-warga.index', $household->exists ? ['household' => $household->id] : []) }}" class="lw-panel-page-back">← Kembali ke data warga</a>
    @endif
</p>

<article class="lw-panel-card lw-panel-card--full">
    <h2 class="lw-panel-card-title">{{ $household->exists ? 'Edit kartu keluarga' : 'Tambah kartu keluarga' }}</h2>

    <form method="POST" action="{{ $household->exists ? route('rt.households.update', $household) : route('rt.households.store') }}" class="lw-panel-form lw-panel-form--wide lw-panel-form--in-card">
        @csrf
        @if($household->exists) @method('PUT') @endif
        @if(! empty($pendataanReturn))
            <input type="hidden" name="return" value="pendataan">
            <input type="hidden" name="pendataan_head" value="{{ $pendataanReturn }}">
            <input type="hidden" name="household_id" value="{{ $household->id }}">
        @endif

        <div class="lw-panel-table-wrap">
            <table class="lw-panel-table lw-rt-resident-detail-table">
                <tbody>
                    @include('rt.residents._household-kk-table', [
                        'mode' => 'edit',
                        'household' => $household,
                        'resident' => null,
                    ])
                </tbody>
            </table>
        </div>

        @if(! $household->exists)
            <p class="lw-panel-field-hint lw-mt-3">
                Setelah KK disimpan, Anda dapat langsung menambahkan anggota keluarga dari halaman Data warga lengkap.
            </p>
        @endif

        <div class="lw-panel-form-actions">
            <button type="submit" class="lw-panel-btn">Simpan</button>
            <a href="{{ route('rt.data-warga.index', $household->exists ? ['household' => $household->id] : []) }}" class="lw-panel-btn lw-panel-btn--secondary">Batal</a>
        </div>
        @if($household->exists && $household->updated_at)
            <p class="lw-rt-resident-last-updated">
                Terakhir diperbarui: {{ $household->updated_at->timezone('Asia/Jayapura')->format('d/m/Y H:i') }}
            </p>
        @endif
    </form>

    @if($household->exists)
        @include('rt.partials.delete-danger-zone', [
            'description' => 'Mengajukan penghapusan permanen kartu keluarga dan semua anggota ke admin sistem. Memerlukan tanda tangan Ketua RT.',
            'label' => 'Hapus KK permanen',
            'confirm' => 'Ajukan hapus permanen kartu keluarga '.$household->family_card_number.' beserta semua anggota? Data akan dihapus setelah admin sistem menyetujui.',
            'action' => route('rt.households.destroy', $household),
            'hidden' => [],
            'enabled' => $household->canBePermanentlyDeleted(),
            'disabledTitle' => $household->deletionBlockReason(),
        ])
    @endif
</article>
</div>
@endsection
