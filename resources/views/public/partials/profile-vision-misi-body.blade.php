{{-- Expects $text (misi string) --}}
@php
    $text = trim($text ?? '');
    $items = $text !== '' ? preg_split('/\s*(?=\d+\.\s)/', $text, -1, PREG_SPLIT_NO_EMPTY) : [];
    $useList = count($items) > 1;
@endphp
@if($useList)
    <ol class="lw-profile-vision-list">
        @foreach($items as $item)
            <li class="lw-profile-vision-text">{{ preg_replace('/^\d+\.\s*/', '', trim($item)) }}</li>
        @endforeach
    </ol>
@else
    <span class="lw-profile-vision-text">{{ $text }}</span>
@endif
