@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<div class="lw-home-page">
    @include('public.partials.home.hero')
    @include('public.partials.home.intro')
    @include('public.partials.home.features-main')
    @include('public.partials.home.faq', ['homeFaq' => $homeFaq])
</div>
@endsection
