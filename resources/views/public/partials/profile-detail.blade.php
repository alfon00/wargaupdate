{{-- Expects $rtProfile (RtProfile) with registered staff --}}

<x-profile-detail-expand
    variant="rt"
    aria-labelledby="profile-detail-title-{{ $rtProfile->slug }}"
>
    <div class="lw-profile-detail-grid lw-profile-detail-grid--compact lw-profile-detail-summary">
        @include('public.partials.profile-photo-slot', [
            'photoUrl' => $rtProfile->publicLeadPhotoUrl(),
            'name' => $rtProfile->publicLeadName(),
            'variant' => 'rt',
            'compact' => true,
        ])
        <div class="lw-profile-detail-body lw-profile-detail-body--fill">
            <h2 id="profile-detail-title-{{ $rtProfile->slug }}" class="lw-profile-detail-title">
                {{ $rtProfile->displayName() }}
            </h2>
            <p class="lw-profile-detail-subtitle">
                @if(filled($rtProfile->rw_number))
                    RW {{ $rtProfile->rw_number }}
                    <span class="lw-profile-detail-subtitle-sep" aria-hidden="true">·</span>
                @endif
                {{ config('kelurahan.distrik') }}
            </p>

            @include('public.partials.profile-detail-rt-vision', ['rtProfile' => $rtProfile])

            @include('public.partials.profile-detail-rt-meta', ['rtProfile' => $rtProfile])
        </div>
    </div>
</x-profile-detail-expand>
