@php
    $label = $label ?? 'Hapus';
    $confirm = $confirm ?? 'Yakin ingin menghapus? Tindakan ini tidak dapat dibatalkan.';
    $hidden = $hidden ?? [];
    $enabled = $enabled ?? true;
    $disabledTitle = $disabledTitle ?? null;
    $buttonClass = $buttonClass ?? 'lw-panel-btn lw-panel-btn--danger lw-panel-btn--sm';
@endphp

@if($enabled)
    <button type="button"
        class="lw-rt-delete-trigger {{ $buttonClass }}"
        data-delete-action="{{ $action }}"
        data-delete-confirm="{{ $confirm }}"
        data-delete-hidden='@json($hidden)'>
        {{ $label }}
    </button>
@else
    <span class="{{ $buttonClass }} is-disabled" role="button" aria-disabled="true"
        @if($disabledTitle) title="{{ $disabledTitle }}" @endif>{{ $label }}</span>
@endif
