@extends('layouts.panel')

@section('title', 'Dashboard Monitoring')

@section('content')
<div class="lw-kel-page">
@include('kelurahan.partials.page-head', [
    'title' => 'Dashboard Monitoring',
    'lead' => 'Ringkasan data warga dan permohonan seluruh RT. Mode baca saja — status tidak dapat diubah dari panel ini.',
])

@include('panel.operational-dashboard')
</div>
@endsection
