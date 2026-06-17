{{-- Expects $rtProfile --}}
@php
    $contactEmail = $rtProfile->publicContactEmail();
    $hasMeta = filled($rtProfile->jam_layanan)
        || filled($rtProfile->alamat_kantor)
        || filled($contactEmail)
        || filled($rtProfile->ketua_rw);
@endphp
@if($hasMeta)
<dl class="lw-profile-meta-grid">
    @if($rtProfile->jam_layanan)
        <div class="lw-profile-meta-item">
            <span class="lw-profile-meta-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div class="lw-profile-meta-item-body">
                <dt>Jam layanan</dt>
                <dd>{{ $rtProfile->jam_layanan }}</dd>
            </div>
        </div>
    @endif
    @if($rtProfile->alamat_kantor)
        <div class="lw-profile-meta-item">
            <span class="lw-profile-meta-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            </span>
            <div class="lw-profile-meta-item-body">
                <dt>Alamat kantor RT</dt>
                <dd>{{ $rtProfile->alamat_kantor }}</dd>
            </div>
        </div>
    @endif
    @if(filled($contactEmail))
        <div class="lw-profile-meta-item">
            <span class="lw-profile-meta-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </span>
            <div class="lw-profile-meta-item-body">
                <dt>Email</dt>
                <dd><a href="mailto:{{ $contactEmail }}" class="lw-profile-phone-link">{{ $contactEmail }}</a></dd>
            </div>
        </div>
    @endif
    @if($rtProfile->ketua_rw)
        <div class="lw-profile-meta-item">
            <div class="lw-profile-meta-item-body">
                <dt>Ketua RW</dt>
                <dd>{{ $rtProfile->ketua_rw }}</dd>
            </div>
        </div>
    @endif
</dl>
@endif
