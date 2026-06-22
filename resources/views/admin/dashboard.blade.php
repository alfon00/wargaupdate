@extends('layouts.panel')

@section('title', 'Dashboard operasional')

@section('content')
<div class="lw-admin-page">
@include('admin.partials.page-head', [
    'eyebrow' => 'Panel Kelurahan · '.config('kelurahan.nama'),
    'title' => 'Dashboard operasional',
    'lead' => 'Ringkasan data warga, permohonan, dan monitoring kependudukan seluruh RT. Mode baca saja — status tidak dapat diubah dari panel ini.',
])

@include('panel.operational-dashboard')

@include('panel.population-monitoring')
</div>
@endsection
