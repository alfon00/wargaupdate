<div class="lw-home-intro-advantages" aria-labelledby="home-advantages-heading">
    <h3 id="home-advantages-heading" class="lw-section-title">Keunggulan sistem</h3>
    <div class="lw-home-advantage-grid">
        @foreach($advantages as $index => $advantage)
            <article class="lw-home-advantage-card">
                <span class="lw-home-advantage-icon" aria-hidden="true">
                    @if($index === 0)
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    @elseif($index === 1)
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                    @endif
                </span>
                <h4 class="lw-home-advantage-title">{{ $advantage['title'] }}</h4>
                <p class="lw-home-advantage-desc">{{ $advantage['desc'] }}</p>
            </article>
        @endforeach
    </div>
</div>
