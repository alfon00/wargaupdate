<section class="lw-home-section lw-home-faq lw-band--alt" id="panduan" aria-labelledby="home-faq-heading" tabindex="-1">
    <div class="lw-container">
    <div class="lw-home-section-head">
        <span class="lw-section-tag">Panduan</span>
        <h2 id="home-faq-heading" class="lw-section-title">Panduan Penggunaan Layanan</h2>
        <p class="lw-section-desc">Pertanyaan umum seputar layanan administrasi RT.</p>
    </div>
    <div class="lw-home-faq-list">
        @foreach($homeFaq as $index => $item)
            <details class="lw-home-faq-item" @if($index === 0) open @endif>
                <summary class="lw-home-faq-question">
                    <span class="lw-home-faq-q-text">{{ $item['question'] }}</span>
                    <span class="lw-home-faq-chevron" aria-hidden="true"></span>
                </summary>
                <div class="lw-home-faq-answer">
                    <p>{{ $item['answer'] }}</p>
                </div>
            </details>
        @endforeach
    </div>
    </div>
</section>
