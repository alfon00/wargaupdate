@props(['action', 'resetUrl', 'showReset' => null])

@php
    $showReset = $showReset ?? collect(request()->query())->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

<form method="GET" action="{{ $action }}" class="lw-kel-filter-bar lw-kel-no-print">
    <div class="lw-kel-filter-grid">
        {{ $slot }}
    </div>
    <div class="lw-kel-filter-actions">
        <button type="submit" class="lw-panel-btn lw-panel-btn--sm">Terapkan</button>
        @if($showReset)
            <a href="{{ $resetUrl }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Reset</a>
        @endif
    </div>
</form>
