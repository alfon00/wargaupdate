@props([
    'items' => [],
    'title' => 'Persyaratan',
])

@if(count($items) > 0)
<div {{ $attributes->merge(['class' => 'lw-form-callout']) }}>
    <p class="lw-form-callout-title">{{ $title }}</p>
    <ol class="lw-form-callout-list">
        @foreach($items as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ol>
</div>
@endif
