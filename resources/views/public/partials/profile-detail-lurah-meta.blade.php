{{-- Expects $lurah array --}}
@php
    $officeAddress = $lurah['alamat_kantor'] ?? config('kelurahan.lurah.alamat_kantor');
    $hasMeta = filled($lurah['telepon'] ?? null)
        || filled($lurah['whatsapp'] ?? null)
        || filled($lurah['email'] ?? null)
        || filled($lurah['jam_layanan'] ?? null)
        || filled($officeAddress);
@endphp
@if($hasMeta)
<dl class="lw-profile-meta-grid">
    @if(! empty($lurah['telepon']))
        <div class="lw-profile-meta-item">
            <span class="lw-profile-meta-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </span>
            <div class="lw-profile-meta-item-body">
                <dt>Telepon</dt>
                <dd><a href="tel:{{ preg_replace('/\s+/', '', $lurah['telepon']) }}" class="lw-profile-phone-link">{{ $lurah['telepon'] }}</a></dd>
            </div>
        </div>
    @endif
    @if(! empty($lurah['whatsapp']))
        <div class="lw-profile-meta-item">
            <span class="lw-profile-meta-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            </span>
            <div class="lw-profile-meta-item-body">
                <dt>WhatsApp</dt>
                <dd><a href="https://wa.me/{{ preg_replace('/\D/', '', $lurah['whatsapp']) }}" class="lw-profile-phone-link" rel="noopener noreferrer">{{ $lurah['whatsapp'] }}</a></dd>
            </div>
        </div>
    @endif
    @if(! empty($lurah['email']))
        <div class="lw-profile-meta-item">
            <span class="lw-profile-meta-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </span>
            <div class="lw-profile-meta-item-body">
                <dt>Email</dt>
                <dd><a href="mailto:{{ $lurah['email'] }}" class="lw-profile-phone-link">{{ $lurah['email'] }}</a></dd>
            </div>
        </div>
    @endif
    @if(! empty($lurah['jam_layanan']))
        <div class="lw-profile-meta-item">
            <span class="lw-profile-meta-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div class="lw-profile-meta-item-body">
                <dt>Jam layanan</dt>
                <dd>{{ $lurah['jam_layanan'] }}</dd>
            </div>
        </div>
    @endif
    @if(filled($officeAddress))
        <div class="lw-profile-meta-item">
            <span class="lw-profile-meta-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            </span>
            <div class="lw-profile-meta-item-body">
                <dt>Alamat kantor</dt>
                <dd>{{ $officeAddress }}</dd>
            </div>
        </div>
    @endif
</dl>
@endif
