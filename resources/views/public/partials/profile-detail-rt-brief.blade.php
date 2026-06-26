{{-- Expects $ketuaUsers --}}
@php
    $primaryKetua = $ketuaUsers->first();
@endphp
<ul class="lw-profile-detail-brief">
    @if($primaryKetua)
        <li>
            <strong>Ketua RT:</strong> {{ $primaryKetua->name }}
            @if($primaryKetua->phone)
                · <a href="tel:{{ preg_replace('/\s+/', '', $primaryKetua->phone) }}" class="lw-profile-phone-link">{{ $primaryKetua->phone }}</a>
            @endif
        </li>
    @endif
</ul>
