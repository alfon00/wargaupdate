@php
    $variant = $variant ?? 'footer';
    $showHeading = $showHeading ?? ($variant === 'page');
    $sosial = config('kelurahan.sosial', []);
    $socialLinks = [
        'instagram' => ['label' => 'Instagram', 'url' => $sosial['instagram'] ?? null],
        'facebook' => ['label' => 'Facebook', 'url' => $sosial['facebook'] ?? null],
        'youtube' => ['label' => 'YouTube', 'url' => $sosial['youtube'] ?? null],
    ];
    $hasSocial = collect($socialLinks)->contains(fn ($item) => filled($item['url']));
    $iconClass = $variant === 'page' ? 'lw-footer-social-icon lw-footer-social-icon--page' : 'lw-footer-social-icon';
@endphp
@if($hasSocial)
    <div class="lw-social-block lw-social-block--{{ $variant }}">
        @if($showHeading)
            <p id="kegiatan-social-heading" class="lw-social-block-label">Media sosial</p>
            <p class="lw-social-block-lead">Ikuti informasi resmi portal. Akun media sosial tiap RT akan ditampilkan di halaman Profil RT.</p>
        @endif
        <nav class="lw-footer-social lw-footer-social--{{ $variant }}" aria-label="Media sosial portal">
            @foreach($socialLinks as $key => $item)
                @if(filled($item['url']))
                    <a href="{{ $item['url'] }}"
                        class="lw-footer-social-link lw-footer-social-link--{{ $key }} lw-footer-social-link--{{ $variant }}"
                        target="_blank"
                        rel="noopener noreferrer">
                        @include('layouts.partials.social-icon', ['platform' => $key, 'class' => $iconClass])
                        <span class="lw-footer-social-label">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>
    </div>
@endif
