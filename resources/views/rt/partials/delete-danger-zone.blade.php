@php
    $title = $title ?? 'Zona berbahaya';
    $description = $description ?? 'Mengajukan penghapusan permanen ke admin kelurahan. Memerlukan tanda tangan Ketua RT dan persetujuan admin kelurahan.';
    $label = $label ?? 'Ajukan hapus permanen';
    $confirm = $confirm ?? 'Ajukan hapus permanen? Data akan dihapus setelah admin kelurahan menyetujui.';
@endphp

<section class="lw-panel-danger-zone lw-mt-6" aria-labelledby="rt-delete-danger-title">
    <h2 id="rt-delete-danger-title" class="lw-panel-section-title lw-panel-section-title--danger">{{ $title }}</h2>
    <p class="lw-panel-card-note lw-mb-4">{{ $description }}</p>
    @include('rt.partials.delete-action', [
        'action' => $action,
        'label' => $label,
        'confirm' => $confirm,
        'hidden' => $hidden ?? [],
        'enabled' => $enabled ?? true,
        'disabledTitle' => $disabledTitle ?? null,
        'buttonClass' => 'lw-panel-btn lw-panel-btn--danger',
    ])
</section>
