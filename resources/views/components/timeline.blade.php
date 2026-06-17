@props([
    'steps' => [],
    'current' => null,
])

<ol class="lw-timeline" aria-label="Progres permohonan">
    @foreach($steps as $step)
        @php
            $state = $step['state'] ?? 'pending';
            $isCurrent = ($current !== null && ($step['key'] ?? null) === $current);
        @endphp
        <li class="lw-timeline-item lw-timeline-item--{{ $state }}{{ $isCurrent ? ' lw-timeline-item--current' : '' }}">
            <span class="lw-timeline-marker" aria-hidden="true">
                @if($state === 'done')
                    <span class="lw-timeline-check">✓</span>
                @elseif($state === 'active' || $isCurrent)
                    <span class="lw-timeline-dot"></span>
                @else
                    <span class="lw-timeline-dot lw-timeline-dot--muted"></span>
                @endif
            </span>
            <div class="lw-timeline-content">
                <p class="lw-timeline-title">{{ $step['title'] }}</p>
                @if(! empty($step['desc']))
                    <p class="lw-timeline-desc">{{ $step['desc'] }}</p>
                @endif
                @if(! empty($step['date']))
                    <time class="lw-timeline-date" datetime="{{ $step['datetime'] ?? '' }}">{{ $step['date'] }}</time>
                @endif
            </div>
        </li>
    @endforeach
</ol>
