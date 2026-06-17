{{-- Expects $item with judul, foto_url, status, status_label, tanggal_label, waktu_label, lokasi, rt_label, search_text, minggu_ini --}}
<article class="lw-activities-event-card"
    data-status="{{ $item['status'] ?? '' }}"
    data-minggu-ini="{{ ! empty($item['minggu_ini']) ? '1' : '0' }}"
    data-search="{{ $item['search_text'] ?? '' }}">
    <div class="lw-activities-event-card__media">
        <img src="{{ $item['foto_url'] ?? asset('images/kegiatan/placeholder.svg') }}"
            alt="Foto dokumentasi: {{ $item['judul'] }}"
            class="lw-activities-event-card__photo"
            width="120"
            height="90"
            loading="lazy"
            decoding="async">
    </div>
    <div class="lw-activities-event-card__body">
        <div class="lw-activities-event-card__head">
            <h2 class="lw-activities-event-card__title">{{ $item['judul'] }}</h2>
            @if(! empty($item['status_label']))
                <span class="lw-activities-event-card__status lw-activities-event-card__status--{{ $item['status'] }}">{{ $item['status_label'] }}</span>
            @endif
        </div>

        <ul class="lw-activities-event-card__meta">
            @if(! empty($item['tanggal_label']))
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <time datetime="{{ $item['tanggal'] ?? '' }}">{{ $item['tanggal_label'] }}</time>
                </li>
            @endif
            @if(! empty($item['waktu_label']))
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ $item['waktu_label'] }}
                </li>
            @endif
            @if(! empty($item['lokasi']))
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    {{ $item['lokasi'] }}
                </li>
            @endif
        </ul>

        @if(! empty($item['rt_label']))
            <span class="lw-activities-event-card__category">{{ $item['rt_label'] }}</span>
        @endif
    </div>
</article>
