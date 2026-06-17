{{-- Expects: $steps (array of title/desc), $sectionId (html id), $headingId; optional: $embedded, $sectionTitle, $intro --}}
@php($embedded = $embedded ?? false)
@if($embedded)
<div class="lw-home-intro-alur" id="{{ $sectionId }}" aria-labelledby="{{ $headingId }}">
    <header class="lw-home-intro-alur-head">
        <h3 id="{{ $headingId }}" class="lw-section-title">{{ $sectionTitle ?? 'Alur layanan' }}</h3>
        <p class="lw-section-desc lw-home-process-lead">{{ $intro ?? 'Dari pengajuan hingga selesai — langkah berurutan.' }}</p>
    </header>
    <div class="lw-flow-grid">
        @foreach($steps as $index => $step)
            <div class="lw-flow-step">
                <span class="lw-flow-step-num" aria-hidden="true">{{ $index + 1 }}</span>
                <div class="lw-flow-step-body">
                    <h4 class="lw-flow-step-title">{{ $step['title'] }}</h4>
                    <p class="lw-flow-step-desc">{{ $step['desc'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
@else
<section class="lw-home-section lw-home-process lw-services-section" id="{{ $sectionId }}" aria-labelledby="{{ $headingId }}" tabindex="-1">
    <div class="lw-container">
        <header class="lw-home-section-head">
            <h2 id="{{ $headingId }}" class="lw-section-title">{{ $sectionTitle ?? 'Alur permohonan' }}</h2>
            <p class="lw-section-desc lw-home-process-lead">{{ $intro ?? 'Dari pengajuan hingga selesai — langkah berurutan.' }}</p>
        </header>
        <div class="lw-flow-grid">
            @foreach($steps as $index => $step)
                <div class="lw-flow-step">
                    <span class="lw-flow-step-num" aria-hidden="true">{{ $index + 1 }}</span>
                    <div class="lw-flow-step-body">
                        <h3 class="lw-flow-step-title">{{ $step['title'] }}</h3>
                        <p class="lw-flow-step-desc">{{ $step['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
