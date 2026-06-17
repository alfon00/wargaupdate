@php
    /** @var \App\Support\SuratFaceReadiness $readiness */
@endphp
<span class="lw-rt-surat-readiness {{ $readiness->adminBadgeClass() }}" title="{{ $readiness->adminTooltip() }}">
    {{ $readiness->adminLabel }}
</span>
