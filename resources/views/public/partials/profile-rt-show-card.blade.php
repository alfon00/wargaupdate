@php
    $kel = config('kelurahan');
    $leadName = $rtProfile->publicLeadName();
    $photoUrl = $rtProfile->publicLeadPhotoUrl();
    $ketuaUsers = $rtProfile->registeredKetuaUsers();
    $contactPhone = $rtProfile->publicContactPhone();
    $contactEmail = $rtProfile->publicContactEmail();
    $hasContacts = filled($contactPhone)
        || filled($contactEmail)
        || filled($rtProfile->jam_layanan)
        || filled($rtProfile->alamat_kantor);
@endphp

<article class="lw-profile-rt-show-card" aria-labelledby="rt-show-heading">
    <header class="lw-profile-rt-show-card__header">
        <span class="lw-profile-rt-chip">{{ $rtProfile->displayName() }}</span>
        @if(filled($rtProfile->rw_number))
            <span class="lw-profile-rw-chip">RW {{ $rtProfile->rw_number }}</span>
        @endif
    </header>

    <div class="lw-profile-rt-show-card__body">
        <div class="lw-profile-rt-show-card__photo" aria-hidden="true">
            @if($photoUrl)
                <img src="{{ $photoUrl }}"
                    alt="Foto {{ $leadName }}"
                    class="lw-profile-rt-show-card__img"
                    width="128"
                    height="128"
                    loading="lazy"
                    decoding="async">
            @else
                <x-photo-empty :name="$leadName" size="fill" class="lw-profile-rt-show-card__photo-empty" />
            @endif
        </div>

        <div class="lw-profile-rt-show-card__content">
            <h1 id="rt-show-heading" class="lw-profile-rt-show-card__name">{{ $leadName }}</h1>
            <p class="lw-profile-rt-show-card__role">
                Ketua {{ $rtProfile->displayName() }}
                @if(filled($rtProfile->rw_number))
                    · RW {{ $rtProfile->rw_number }}
                @endif
                · {{ $kel['distrik'] }}
            </p>

            @if($ketuaUsers->isNotEmpty())
                <ul class="lw-profile-rt-show-card__staff">
                    @foreach($ketuaUsers as $ketua)
                        <li>
                            <span class="lw-profile-rt-show-card__staff-role">Ketua RT</span>
                            <span class="lw-profile-rt-show-card__staff-name">{{ $ketua->name }}</span>
                            @if(filled($ketua->phone))
                                <a href="tel:{{ preg_replace('/\s+/', '', $ketua->phone) }}" class="lw-profile-rt-show-card__staff-contact">{{ $ketua->phone }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            <p class="lw-profile-rt-show-card__stats">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                @if(($residentCount ?? 0) > 0)
                    {{ number_format($residentCount, 0, ',', '.') }} warga terdaftar
                @else
                    Belum ada data warga
                @endif
            </p>

            @if(filled($rtProfile->ketua_rw))
                <p class="lw-profile-rt-show-card__rw">Ketua RW: {{ $rtProfile->ketua_rw }}</p>
            @endif

            @if($hasContacts)
                <ul class="lw-profile-rt-show-card__contacts">
                    @if(filled($contactPhone))
                        <li>
                            <a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}" class="lw-profile-rt-show-card__contact">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                {{ $contactPhone }}
                            </a>
                        </li>
                    @endif
                    @if(filled($contactEmail))
                        <li>
                            <a href="mailto:{{ $contactEmail }}" class="lw-profile-rt-show-card__contact">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                {{ $contactEmail }}
                            </a>
                        </li>
                    @endif
                    @if(filled($rtProfile->jam_layanan))
                        <li>
                            <span class="lw-profile-rt-show-card__contact lw-profile-rt-show-card__contact--static">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $rtProfile->jam_layanan }}
                            </span>
                        </li>
                    @endif
                    @if(filled($rtProfile->alamat_kantor))
                        <li>
                            <span class="lw-profile-rt-show-card__contact lw-profile-rt-show-card__contact--static">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $rtProfile->alamat_kantor }}
                            </span>
                        </li>
                    @endif
                </ul>
            @endif
        </div>
    </div>
</article>
