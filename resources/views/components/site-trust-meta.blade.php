@props([
    'canonical' => null,
    'description' => 'Portal layanan surat pengantar RT. Bukan situs Dukcapil, Kemendagri, atau bank.',
])

@php
    $schemaOrg = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => config('kelurahan.portal_nama_schema'),
        'url' => config('app.url'),
        'description' => 'Layanan administrasi RT/RW. Bukan situs Dukcapil atau Kemendagri.',
        'areaServed' => [
            '@type' => 'AdministrativeArea',
            'name' => config('kelurahan.distrik').', '.config('kelurahan.provinsi'),
        ],
    ];
@endphp

@include('layouts.partials.favicon')
<link rel="canonical" href="{{ $canonical ?? url()->current() }}">
<meta name="description" content="{{ $description }}">
<meta name="robots" content="index, follow">
<meta name="application-name" content="{{ config('kelurahan.portal_nama') }}">
<script type="application/ld+json">{!! json_encode($schemaOrg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
