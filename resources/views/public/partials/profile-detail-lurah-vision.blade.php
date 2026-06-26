{{-- Expects $lurah array --}}
@php
    $hasVisi = filled($lurah['visi'] ?? null);
    $hasMisi = filled($lurah['misi'] ?? null);
@endphp
<dl class="lw-profile-detail-vision lw-profile-detail-vision--summary">
    <div>
        <dt>Visi</dt>
        <dd>
            @if($hasVisi)
                <span class="lw-profile-vision-text">{{ $lurah['visi'] }}</span>
            @else
                <x-profile-content-empty tag="span">Belum diisi</x-profile-content-empty>
            @endif
        </dd>
    </div>
    <div>
        <dt>Misi</dt>
        <dd>
            @if($hasMisi)
                @include('public.partials.profile-vision-misi-body', ['text' => $lurah['misi']])
            @else
                <x-profile-content-empty tag="span">Belum diisi</x-profile-content-empty>
            @endif
        </dd>
    </div>
</dl>
