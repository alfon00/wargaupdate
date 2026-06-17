@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="lw-profile-page">
    @include('public.partials.profile.hero')

    <div class="lw-container lw-profile-board">
        @include('public.partials.lurah-profile')

        @include('public.partials.profile-rt-grid', [
            'profiles' => $profiles,
            'residentCounts' => $residentCounts,
            'highlightSlug' => $highlightSlug ?? null,
        ])

        @include('public.partials.profile-wilayah-info')
    </div>
</div>

@if(! empty($highlightSlug))
@push('scripts')
<script>
(function () {
    var card = document.getElementById('rt-card-{{ $highlightSlug }}');
    if (card) {
        requestAnimationFrame(function () {
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }
})();
</script>
@endpush
@endif
@endsection
