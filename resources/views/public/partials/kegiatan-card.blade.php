{{-- Expects $item with judul, tanggal, tanggal_label, ringkasan, lokasi, foto_url, rt_label --}}
<article class="lw-kegiatan-card">
    <div class="lw-kegiatan-photo-wrap">
        <img
            src="{{ $item['foto_url'] ?? asset('images/kegiatan/placeholder.svg') }}"
            alt="Foto dokumentasi: {{ $item['judul'] }}"
            class="lw-kegiatan-photo"
            width="640"
            loading="lazy"
            decoding="async"
        >
    </div>
    <div class="lw-kegiatan-card-inner">
        @if(! empty($item['rt_label']) || ! empty($item['tanggal_label']) || ! empty($item['lokasi']))
            <div class="lw-kegiatan-card-meta">
                @if(! empty($item['rt_label']))
                    <span class="lw-kegiatan-rt-badge">{{ $item['rt_label'] }}</span>
                @endif
                @if(! empty($item['tanggal_label']))
                    <time class="lw-kegiatan-date" datetime="{{ $item['tanggal'] ?? '' }}">
                        <span class="lw-kegiatan-meta-icon" aria-hidden="true">📅</span>
                        {{ $item['tanggal_label'] }}
                    </time>
                @endif
                @if(! empty($item['lokasi']))
                    <span class="lw-kegiatan-lokasi-inline">
                        <span class="lw-kegiatan-meta-icon" aria-hidden="true">📍</span>
                        {{ $item['lokasi'] }}
                    </span>
                @endif
            </div>
        @endif
        <h3 class="lw-kegiatan-card-name">{{ $item['judul'] }}</h3>
        @if(! empty($item['ringkasan']))
            <p class="lw-kegiatan-card-desc">{{ $item['ringkasan'] }}</p>
        @endif
    </div>
</article>
