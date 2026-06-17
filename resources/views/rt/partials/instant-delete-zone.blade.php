@php
    $title = $title ?? 'Zona berbahaya';
    $description = $description ?? 'Menghapus data ini secara permanen dari sistem. Tindakan tidak dapat dibatalkan.';
    $label = $label ?? 'Hapus';
    $confirm = $confirm ?? 'Yakin ingin menghapus? Tindakan ini tidak dapat dibatalkan.';
@endphp

<section class="lw-panel-danger-zone lw-mt-6" aria-labelledby="rt-instant-delete-title">
    <h2 id="rt-instant-delete-title" class="lw-panel-section-title lw-panel-section-title--danger">{{ $title }}</h2>
    <p class="lw-panel-card-note lw-mb-4">{{ $description }}</p>
    <form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($confirm));">
        @csrf
        @method('DELETE')
        <button type="submit" class="lw-panel-btn lw-panel-btn--danger">{{ $label }}</button>
    </form>
</section>
