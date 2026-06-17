@php
    $deleteLabel = $deleteLabel ?? 'Hapus';
    $confirm = $confirm ?? 'Yakin ingin menghapus? Tindakan ini tidak dapat dibatalkan.';
@endphp
<form method="POST" action="{{ $action }}" class="inline ml-2" onsubmit="return confirm(@js($confirm));">
    @csrf
    @method('DELETE')
    <button type="submit" class="lw-panel-table-link lw-panel-table-link--danger">{{ $deleteLabel }}</button>
</form>
