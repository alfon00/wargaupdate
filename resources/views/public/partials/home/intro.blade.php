<section class="lw-home-section lw-home-intro" aria-labelledby="home-intro-heading">
    <div class="lw-container">
    <div class="lw-home-section-head">
        <span class="lw-section-tag">Platform</span>
        <h2 id="home-intro-heading" class="lw-section-title">Mengenal Platform Layanan RT Inauga</h2>
    </div>
    <p class="lw-home-intro-lead">
        {{ $platformIntroLead }}
    </p>
    @include('public.partials.home.platform-advantages', ['advantages' => $platformAdvantages])
    </div>
</section>
