@php
    /** @var \Illuminate\Support\Collection<int, array{label: string, meta: string, url: string, tone: string}> $priorities */
    $toneClass = static fn (string $tone): string => match ($tone) {
        'danger' => 'lw-rt-priority-item--danger',
        'info' => 'lw-rt-priority-item--info',
        default => 'lw-rt-priority-item--warn',
    };
@endphp

<ul class="lw-rt-priority-list">
    @foreach($priorities as $item)
        <li class="lw-rt-priority-item {{ $toneClass($item['tone'] ?? 'warn') }}">
            <div class="lw-rt-priority-item__body">
                <p class="lw-rt-priority-item__label">{{ $item['label'] }}</p>
                @if(filled($item['meta'] ?? null))
                    <p class="lw-rt-priority-item__meta">{{ $item['meta'] }}</p>
                @endif
            </div>
            <a href="{{ $item['url'] }}" class="lw-rt-priority-item__link">Lihat →</a>
        </li>
    @endforeach
</ul>
