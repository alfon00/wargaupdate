@php
    $kel = config('kelurahan');
    $lurah = \App\Models\KelurahanOfficial::publicLurahArray();
    $lurahPhoto = $lurah['photo'] ?? null;
    $lurahName = $lurah['nama'] ?? 'Lurah';
    $initial = mb_strtoupper(mb_substr(preg_replace('/^[^A-Za-z0-9]+/u', '', $lurahName) ?: 'L', 0, 1));

    $instagramUrl = config('kelurahan.sosial.instagram');
    $instagramHandle = null;
    if (filled($instagramUrl) && preg_match('/instagram\.com\/([^\/?#]+)/i', $instagramUrl, $m)) {
        $instagramHandle = $m[1] ?? null;
    }
@endphp

<section class="lw-profile-lurah-card" id="lurah" aria-labelledby="lurah-profile-heading" tabindex="-1">
    <header class="lw-profile-section-head lw-home-section-head">
        <h2 id="lurah-profile-heading" class="lw-section-title lw-mt-2">Profil Lurah</h2>
    </header>

    <article class="lw-profile-lurah-card__body">
        <div class="lw-profile-lurah-card__photo" aria-hidden="true">
            @if($lurahPhoto)
                <img src="{{ $lurahPhoto }}"
                    alt="Foto {{ $lurahName }}"
                    class="lw-profile-lurah-card__img"
                    width="128"
                    height="128"
                    loading="lazy"
                    decoding="async">
            @else
                <div class="lw-profile-lurah-card__placeholder" role="img" aria-label="Belum ada foto profil {{ $lurahName }}">
                    <span class="lw-profile-lurah-card__initial">{{ $initial }}</span>
                </div>
            @endif
        </div>

        <div class="lw-profile-lurah-card__content">
            <h3 id="lurah-profile-name" class="lw-profile-lurah-card__name">{{ $lurahName }}</h3>
            <p class="lw-profile-lurah-card__role">
                {{ $lurah['jabatan'] ?? 'Lurah' }} · {{ $kel['distrik'] }}
            </p>

            @include('public.partials.profile-detail-lurah-vision')

            @php
                $hasContacts = filled($lurah['email'] ?? null)
                    || filled($lurah['telepon'] ?? null)
                    || filled($lurah['whatsapp'] ?? null)
                    || filled($instagramUrl)
                    || filled($lurah['jam_layanan'] ?? null);
            @endphp
            @if($hasContacts)
                <ul class="lw-profile-lurah-card__contacts">
                    @if(! empty($lurah['email']))
                        <li>
                            <a href="mailto:{{ $lurah['email'] }}" class="lw-profile-lurah-card__contact">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                Email kantor: {{ $lurah['email'] }}
                            </a>
                        </li>
                    @endif

                    @if(filled($instagramUrl))
                        <li>
                            <a href="{{ $instagramUrl }}" class="lw-profile-lurah-card__contact" target="_blank" rel="noopener noreferrer">
                                @include('layouts.partials.social-icon', [
                                    'platform' => 'instagram',
                                    'class' => 'lw-footer-social-icon',
                                ])
                                {{ filled($instagramHandle) ? 'Instagram kantor: @'.$instagramHandle : 'Instagram kantor' }}
                            </a>
                        </li>
                    @endif

                    @if(! empty($lurah['telepon']))
                        <li>
                            <a href="tel:{{ preg_replace('/\s+/', '', $lurah['telepon']) }}" class="lw-profile-lurah-card__contact">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                {{ $lurah['telepon'] }}
                            </a>
                        </li>
                    @endif
                    @if(! empty($lurah['whatsapp']))
                        <li>
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', $lurah['whatsapp']) }}" class="lw-profile-lurah-card__contact" rel="noopener noreferrer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                                {{ $lurah['whatsapp'] }}
                            </a>
                        </li>
                    @endif
                    @if(! empty($lurah['jam_layanan']))
                        <li>
                            <span class="lw-profile-lurah-card__contact lw-profile-lurah-card__contact--static">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $lurah['jam_layanan'] }}
                            </span>
                        </li>
                    @endif
                </ul>
            @endif
        </div>
    </article>
</section>
