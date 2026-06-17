@php
    /** @var \App\Models\Resident $resident */
    $listQuery = $listQuery ?? [];
    $deleteHidden = array_filter([
        'filter' => $listQuery['filter'] ?? request('filter', 'aktif'),
        'kategori' => $listQuery['kategori'] ?? request('kategori'),
        'household' => $listQuery['household'] ?? request('household', $resident->household_id),
    ], fn ($value) => filled($value) && $value !== 'semua');
    $hasPendingDeletion = $resident->hasPendingDeletionRequest();
    $rejectedDeletion = $resident->latestRejectedDeletionRequest();
    $canDelete = $resident->canBePermanentlyDeleted();
    $deleteReason = $resident->deletionBlockReason();
    $isArchived = $resident->domicile_status?->isArchived() ?? false;
    $deleteConfirm = 'Ajukan hapus permanen data '.$resident->name.'? Data akan dihapus setelah admin sistem menyetujui.';
@endphp

<section id="kelola-data-warga" aria-labelledby="rt-resident-manage-title">
    <h2 id="rt-resident-manage-title" class="lw-panel-section-title lw-panel-section-title--danger">Kelola data warga</h2>

    @if($hasPendingDeletion)
        <div class="lw-panel-alert lw-panel-alert--warn lw-mb-4" role="status">
            Pengajuan hapus permanen menunggu persetujuan admin sistem. Data belum dihapus.
        </div>
    @elseif($rejectedDeletion)
        <div class="lw-panel-alert lw-panel-alert--error lw-mb-4" role="status">
            Pengajuan hapus permanen terakhir ditolak admin.
            @if($rejectedDeletion->admin_notes)
                Catatan: {{ $rejectedDeletion->admin_notes }}
            @endif
        </div>
    @endif

    @include('rt.partials.delete-danger-zone', [
        'description' => $isArchived
            ? 'Ajukan penghapusan permanen data arsip ke admin sistem. Memerlukan tanda tangan Ketua RT.'
            : 'Ajukan penghapusan permanen ke admin sistem. Memerlukan tanda tangan Ketua RT.',
        'label' => $isArchived ? 'Ajukan hapus arsip permanen' : 'Ajukan hapus permanen',
        'confirm' => $deleteConfirm,
        'action' => route('rt.residents.destroy', $resident),
        'hidden' => $deleteHidden,
        'enabled' => $canDelete,
        'disabledTitle' => $deleteReason,
    ])
</section>
