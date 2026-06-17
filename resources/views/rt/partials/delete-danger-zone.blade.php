@php
    $title = $title ?? 'Zona berbahaya';
    $description = $description ?? 'Mengajukan penghapusan permanen ke admin sistem. Memerlukan tanda tangan Ketua RT dan persetujuan admin.';
    $label = $label ?? 'Ajukan hapus permanen';
    $confirm = $confirm ?? 'Ajukan hapus permanen? Data akan dihapus setelah admin sistem menyetujui.';
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
