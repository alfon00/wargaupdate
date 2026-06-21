<footer class="lw-footer">
    <div class="lw-footer-inner">
        @unless(request()->routeIs('security'))
            <div class="lw-footer-top">
                <x-site-disclaimer variant="footer" />

                <p class="lw-footer-security">
                    <strong>Keamanan & keaslian situs:</strong>
                    Pastikan Anda mengakses portal resmi dengan alamat yang benar.
                    <a href="{{ route('security') }}" class="lw-footer-security-link">Pelajari lebih lanjut →</a>
                </p>
            </div>
        @endunless

        <div class="lw-footer-main">
            <div class="lw-footer-brand">
                <div class="lw-footer-brand-head">
                    <img src="{{ asset(config('kelurahan.portal_logo')) }}" alt="{{ config('kelurahan.portal_nama') }}" class="lw-footer-logo" width="44" height="44" decoding="async">
                    <div class="lw-footer-brand-text">
                        <p class="lw-footer-text">{{ config('kelurahan.portal_nama') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="lw-footer-bottom">
            <p class="lw-footer-copyright">© {{ date('Y') }} {{ config('kelurahan.portal_nama') }}. Semua hak dilindungi.</p>

            <div class="lw-footer-aside">
                @include('layouts.partials.social-links', ['variant' => 'footer', 'showHeading' => false])
            </div>
        </div>
    </div>
</footer>
