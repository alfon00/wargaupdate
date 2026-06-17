{{-- Expects $sekretarisUsers — isi "Lihat detail" (daftar sekretaris lengkap) --}}
@if($sekretarisUsers->isNotEmpty())
<dl class="lw-profile-detail-dl">
    <div>
        <dt>Sekretaris RT</dt>
        <dd>
            <ul class="lw-profile-staff-list">
                @foreach($sekretarisUsers as $sekretaris)
                    <li>
                        <span class="lw-profile-staff-name">{{ $sekretaris->name }}</span>
                        @if($sekretaris->phone)
                            <span class="lw-profile-staff-meta">
                                <a href="tel:{{ preg_replace('/\s+/', '', $sekretaris->phone) }}" class="lw-profile-phone-link">{{ $sekretaris->phone }}</a>
                                <span aria-hidden="true"> · </span>
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $sekretaris->phone) }}" class="lw-profile-phone-link" rel="noopener noreferrer">WhatsApp</a>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </dd>
    </div>
</dl>
@endif
