{{-- Expects: $serviceFlows (from HomeContent::serviceCatalogFlows), $sectionId, $headingId --}}
@php
    $flowKeys = array_keys($serviceFlows);
    $defaultKey = $flowKeys[0] ?? 'surat';
@endphp
<section class="lw-home-section lw-home-process lw-services-section lw-service-flow-tabs" id="{{ $sectionId }}" aria-labelledby="{{ $headingId }}" tabindex="-1">
    <div class="lw-container">
        <header class="lw-home-section-head">
            <h2 id="{{ $headingId }}" class="lw-section-title">Alur layanan</h2>
            <p class="lw-section-desc lw-home-process-lead">Setiap layanan memiliki alur berbeda. Pilih tab di bawah untuk melihat langkah-langkahnya.</p>
        </header>

        <div class="lw-service-flow-tabs__root">
            @foreach($serviceFlows as $key => $flow)
                <input
                    type="radio"
                    name="service-flow-tab"
                    id="flow-tab-{{ $key }}"
                    class="lw-service-flow-tabs__input"
                    @checked($key === $defaultKey)
                    hidden
                >
            @endforeach

            <div class="lw-service-flow-tabs__bar" role="tablist" aria-label="Pilih jenis layanan">
                @foreach($serviceFlows as $key => $flow)
                    <label
                        for="flow-tab-{{ $key }}"
                        id="flow-tab-label-{{ $key }}"
                        class="lw-service-flow-tabs__tab"
                        role="tab"
                        aria-controls="flow-panel-{{ $key }}"
                    >{{ $flow['label'] }}</label>
                @endforeach
            </div>

            @foreach($serviceFlows as $key => $flow)
                <div
                    id="{{ $flow['anchor'] }}"
                    class="lw-service-flow-panel"
                    data-flow-key="{{ $key }}"
                    role="tabpanel"
                    aria-labelledby="flow-tab-label-{{ $key }}"
                    tabindex="0"
                >
                    <p class="lw-service-flow-panel__intro">{{ $flow['intro'] }}</p>
                    <div class="lw-flow-grid">
                        @foreach($flow['steps'] as $index => $step)
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
            @endforeach
        </div>
    </div>
</section>
