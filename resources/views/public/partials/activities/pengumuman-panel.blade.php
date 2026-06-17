@php
    $previewLimit = 3;
    $hasMore = $pengumuman->count() > $previewLimit;
@endphp
<aside class="lw-activities-announce-panel{{ $hasMore ? ' has-more' : '' }}" aria-labelledby="activities-announce-heading">
    <div class="lw-activities-announce-panel__head">
        <h2 id="activities-announce-heading" class="lw-activities-announce-panel__title">Pengumuman</h2>
        @if($hasMore)
            <button type="button" class="lw-activities-announce-panel__toggle" id="lw-activities-announce-toggle" aria-expanded="false">
                Lihat Semua
            </button>
        @endif
    </div>

    @if($pengumuman->isEmpty())
        <p class="lw-kegiatan-empty lw-kegiatan-empty--compact">Belum ada pengumuman.</p>
    @else
        <ul class="lw-activities-announce-list" id="lw-activities-announce-list">
            @foreach($pengumuman as $index => $item)
                <li class="lw-activities-announce-item{{ $index >= $previewLimit ? ' is-collapsed' : '' }}">
                    <article class="lw-activities-announce-card">
                        <div class="lw-activities-announce-card__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        </div>
                        <div class="lw-activities-announce-card__body">
                            <h3 class="lw-activities-announce-card__title">{{ $item['judul'] }}</h3>
                            @if(! empty($item['ringkasan']))
                                <p class="lw-activities-announce-card__summary">{{ $item['ringkasan'] }}</p>
                            @endif
                            <div class="lw-activities-announce-card__foot">
                                @if(! empty($item['rt_label']))
                                    <span class="lw-activities-announce-card__rt">{{ $item['rt_label'] }}</span>
                                @endif
                                @if(! empty($item['tanggal_label']))
                                    <time class="lw-activities-announce-card__date" datetime="{{ $item['tanggal'] ?? '' }}">{{ $item['tanggal_label'] }}</time>
                                @endif
                                @if(! empty($item['berlaku_hingga_label']))
                                    <span class="lw-activities-announce-card__date">Berlaku hingga {{ $item['berlaku_hingga_label'] }}</span>
                                @endif
                            </div>
                        </div>
                    </article>
                </li>
            @endforeach
        </ul>
    @endif
</aside>
