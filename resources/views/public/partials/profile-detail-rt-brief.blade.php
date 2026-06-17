{{-- Expects $ketuaUsers, $sekretarisUsers --}}
@php
    $primaryKetua = $ketuaUsers->first();
    $primarySekretaris = $sekretarisUsers->first();
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
    @if($primarySekretaris)
        <li><strong>Sekretaris RT:</strong> {{ $primarySekretaris->name }}</li>
    @endif
</ul>
