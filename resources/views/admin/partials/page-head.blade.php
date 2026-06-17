@php
    $eyebrow = $eyebrow ?? 'Admin sistem';
    $lead = $lead ?? null;
    $row = $row ?? false;
    $buttonUrl = $buttonUrl ?? null;
    $buttonLabel = $buttonLabel ?? null;
@endphp
<header class="lw-panel-page-head{{ ($row || $buttonUrl) ? ' lw-panel-page-head--row' : '' }}">
    <div>
        <p class="lw-panel-page-eyebrow">{{ $eyebrow }}</p>
        <h1 class="lw-panel-page-title">{{ $title }}</h1>
        @if($lead)
            <p class="lw-panel-page-lead">{{ $lead }}</p>
        @endif
    </div>
    @if($buttonUrl)
        <a href="{{ $buttonUrl }}" class="lw-panel-btn">{{ $buttonLabel }}</a>
    @endif
</header>
