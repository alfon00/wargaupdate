@extends('layouts.document-viewer')

@section('title', $title)

@section('content')
<header class="lw-doc-viewer-header">
    <div class="lw-doc-viewer-header-main">
        <h1 class="lw-doc-viewer-title">{{ $title }}</h1>
        <x-today-date variant="plain" class="lw-doc-viewer-date" />
    </div>
    <div class="lw-doc-viewer-header-actions">
        @if(! empty($downloadUrl))
            <a href="{{ $downloadUrl }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Unduh</a>
        @endif
        <a href="{{ $backUrl }}" class="lw-panel-btn lw-panel-btn--secondary lw-panel-btn--sm">Kembali</a>
    </div>
</header>
<main class="lw-doc-viewer-main">
    <iframe
        src="{{ $pdfUrl }}"
        class="lw-doc-viewer-frame"
        title="{{ $title }}"
    ></iframe>
</main>
@endsection
