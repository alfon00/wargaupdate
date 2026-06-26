@php
    $lurah = \App\Models\KelurahanOfficial::publicLurahArray();
    $lurahName = $lurah['nama'] ?? 'Lurah';
    $photoUrl = $lurah['photo'] ?? null;

    $instagramUrl = config('kelurahan.sosial.instagram');
    $facebookUrl = config('kelurahan.sosial.facebook');
    $youtubeUrl = config('kelurahan.sosial.youtube');
    $instagramHandle = null;
    if (filled($instagramUrl) && preg_match('/instagram\.com\/([^\/?#]+)/i', $instagramUrl, $m)) {
        $instagramHandle = $m[1] ?? null;
    }
@endphp

<section class="lw-profile-kelurahan-section" id="kelurahan" aria-labelledby="kelurahan-profile-heading" tabindex="-1">
    <header class="lw-profile-section-head lw-home-section-head">
        <span class="lw-section-tag">Pemerintah Kelurahan</span>
        <h2 id="kelurahan-profile-heading" class="lw-section-title lw-mt-2">Profil Kelurahan</h2>
    </header>

    <article class="lw-profile-kelurahan-card" aria-labelledby="lurah-profile-name">
        <div class="lw-profile-kelurahan-card__body">
            <div class="lw-profile-kelurahan-card__photo">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}"
                        alt="Foto {{ $lurahName }}"
                        class="lw-profile-kelurahan-card__img"
                        width="128"
                        height="128"
                        loading="lazy"
                        decoding="async">
                @else
                    <x-photo-empty :name="$lurahName" size="fill" class="lw-profile-kelurahan-card__photo-empty" />
                @endif
            </div>

            <div class="lw-profile-kelurahan-card__content">
                <p class="lw-profile-kelurahan-card__eyebrow">Pejabat kelurahan</p>
                <h3 id="lurah-profile-name" class="lw-profile-kelurahan-card__name">{{ $lurahName }}</h3>
                <p class="lw-profile-kelurahan-card__role">{{ $lurah['jabatan'] ?? 'Lurah' }}</p>

                @include('public.partials.profile-detail-lurah-vision')

                @include('public.partials.profile-detail-lurah-meta')

                @if(filled($instagramUrl) || filled($facebookUrl) || filled($youtubeUrl))
                    <ul class="lw-profile-kelurahan-card__social">
                        @if(filled($instagramUrl))
                            <li>
                                <a href="{{ $instagramUrl }}" class="lw-profile-kelurahan-card__social-link" target="_blank" rel="noopener noreferrer">
                                    @include('layouts.partials.social-icon', ['platform' => 'instagram', 'class' => 'lw-footer-social-icon'])
                                    {{ filled($instagramHandle) ? '@'.$instagramHandle : 'Instagram' }}
                                </a>
                            </li>
                        @endif
                        @if(filled($facebookUrl))
                            <li>
                                <a href="{{ $facebookUrl }}" class="lw-profile-kelurahan-card__social-link" target="_blank" rel="noopener noreferrer">
                                    @include('layouts.partials.social-icon', ['platform' => 'facebook', 'class' => 'lw-footer-social-icon'])
                                    Facebook
                                </a>
                            </li>
                        @endif
                        @if(filled($youtubeUrl))
                            <li>
                                <a href="{{ $youtubeUrl }}" class="lw-profile-kelurahan-card__social-link" target="_blank" rel="noopener noreferrer">
                                    @include('layouts.partials.social-icon', ['platform' => 'youtube', 'class' => 'lw-footer-social-icon'])
                                    YouTube
                                </a>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>
        </div>
    </article>
</section>
