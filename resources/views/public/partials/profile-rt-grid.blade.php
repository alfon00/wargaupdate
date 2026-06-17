<section class="lw-profile-rt-section" id="rt" aria-labelledby="rt-profile-heading" tabindex="-1">
    <header class="lw-profile-section-head lw-home-section-head">
        <span class="lw-section-tag">Rukun Tetangga</span>
        <h2 id="rt-profile-heading" class="lw-section-title lw-mt-2">Profil RT</h2>
        <p class="lw-profile-section-lead">
            Daftar RT yang terdaftar di portal. Pilih kartu untuk melihat profil lengkap pengurus dan layanan administrasi.
        </p>
    </header>

    @if($profiles->isEmpty())
        <div class="lw-profile-empty">
            <p class="lw-profile-empty-lead lw-mb-0">Belum ada profil RT aktif di portal. Pengurus RT akan ditampilkan setelah admin mendaftarkan akun.</p>
        </div>
    @else
        <ul class="lw-profile-rt-grid">
            @foreach($profiles as $p)
                @php
                    $residentCount = $residentCounts[$p->id] ?? 0;
                    $leadName = $p->publicLeadName();
                    $photoUrl = $p->publicLeadPhotoUrl();
                    $initial = mb_strtoupper(mb_substr(preg_replace('/^[^A-Za-z0-9]+/u', '', $leadName) ?: 'R', 0, 1));
                    $isHighlighted = isset($highlightSlug) && $highlightSlug === $p->slug;
                @endphp
                <li>
                    <article class="lw-profile-rt-card{{ $isHighlighted ? ' is-highlighted' : '' }}"
                        id="rt-card-{{ $p->slug }}">
                        <div class="lw-profile-rt-card__header">
                            <span class="lw-profile-rt-chip">{{ $p->displayName() }}</span>
                            @if(filled($p->rw_number))
                                <span class="lw-profile-rw-chip">RW {{ $p->rw_number }}</span>
                            @endif
                        </div>

                        <div class="lw-profile-rt-card__photo">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}"
                                    alt="Foto {{ $leadName }}"
                                    class="lw-profile-rt-card__img"
                                    width="80"
                                    height="80"
                                    loading="lazy"
                                    decoding="async">
                            @else
                                <div class="lw-profile-rt-card__placeholder" role="img" aria-label="Belum ada foto {{ $leadName }}">
                                    <span>{{ $initial }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="lw-profile-rt-card__body">
                            <h3 class="lw-profile-rt-card__name">{{ $leadName }}</h3>
                            <p class="lw-profile-rt-card__role">Ketua {{ $p->displayName() }}</p>

                            <p class="lw-profile-rt-card__stats">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                @if($residentCount > 0)
                                    {{ number_format($residentCount, 0, ',', '.') }} warga terdaftar
                                @else
                                    Belum ada data warga
                                @endif
                            </p>
                        </div>

                        <a href="{{ route('profile.show', $p->slug) }}" class="lw-btn-primary lw-profile-rt-card__cta">
                            Lihat detail
                        </a>
                    </article>
                </li>
            @endforeach
        </ul>
    @endif
</section>
