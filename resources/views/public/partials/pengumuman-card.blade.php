<article class="lw-pengumuman-card lw-pengumuman-card--page">
    <div class="lw-pengumuman-card-inner">
        <div class="lw-pengumuman-card-meta">
            @if(! empty($item['rt_label']))
                <span class="lw-kegiatan-rt-badge">{{ $item['rt_label'] }}</span>
            @endif
            @if(! empty($item['tanggal_label']))
                <time class="lw-kegiatan-date" datetime="{{ $item['tanggal'] ?? '' }}">{{ $item['tanggal_label'] }}</time>
            @endif
        </div>
        <h3 class="lw-pengumuman-card-name">{{ $item['judul'] }}</h3>
        @if(! empty($item['ringkasan']))
            <p class="lw-pengumuman-card-desc">{{ $item['ringkasan'] }}</p>
        @endif
    </div>
</article>
