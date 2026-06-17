<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dokumen') — {{ config('kelurahan.portal_nama', 'Layanan Warga') }}</title>
    <meta name="robots" content="noindex, nofollow">
    @include('layouts.partials.favicon')
    @vite(['resources/css/app.css'])
    @include('layouts.partials.lw-styles')
</head>
<body class="lw-doc-viewer-body">
    @yield('content')
</body>
</html>
