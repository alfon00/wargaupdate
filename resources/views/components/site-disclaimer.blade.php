@props(['variant' => 'default'])

@if($variant === 'footer')
<div {{ $attributes->merge(['class' => 'lw-footer-disclaimer']) }}>
    <p class="lw-footer-disclaimer-text">Portal RT Kelurahan Inauga — bukan situs Dukcapil/Kemendagri. Hanya untuk surat pengantar RT. Layanan portal ini gratis untuk warga. Tidak meminta kartu kredit, PIN bank, atau OTP pembayaran.</p>
</div>
@else
<div {{ $attributes->merge(['class' => 'rounded-lg border border-emerald-200 lw-surface px-4 py-3 text-xs text-slate-700 leading-relaxed']) }}>
    <p class="font-semibold text-emerald-900">Portal RT Kelurahan Inauga — bukan situs Dukcapil/Kemendagri</p>
    <p class="mt-1">Hanya untuk surat pengantar RT. Layanan portal ini gratis untuk warga. Tidak meminta kartu kredit, PIN bank, atau OTP pembayaran.</p>
    <a href="{{ route('security') }}" class="mt-1.5 inline-block font-medium text-emerald-800 hover:underline">Keamanan & keaslian situs →</a>
</div>
@endif
