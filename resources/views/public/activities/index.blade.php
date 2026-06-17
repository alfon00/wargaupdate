@extends('layouts.app')

@section('title', 'Kegiatan & Pengumuman')

@section('content')
<div class="lw-kegiatan-page lw-activities-page">
    @include('public.partials.activities.hero')

    <div class="lw-container lw-activities-body">
        @include('public.partials.activities.toolbar')

        <div class="lw-activities-layout">
            <section class="lw-activities-main" aria-labelledby="activities-list-heading">
                <h2 id="activities-list-heading" class="sr-only">Daftar kegiatan</h2>

                @if($kegiatan->isEmpty())
                    <p class="lw-kegiatan-empty">Belum ada kegiatan. Informasi akan muncul setelah pengurus RT menambahkannya di panel.</p>
                @else
                    <div class="lw-activities-event-list" id="lw-activities-event-list">
                        @foreach($kegiatan as $item)
                            @include('public.partials.activities.kegiatan-list-card', ['item' => $item])
                        @endforeach
                    </div>
                    <p class="lw-activities-empty-filter" id="lw-activities-empty-filter" hidden>
                        Tidak ada kegiatan yang cocok dengan filter atau pencarian.
                    </p>
                @endif
            </section>

            @include('public.partials.activities.pengumuman-panel', ['pengumuman' => $pengumuman])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/activities-filter.js') }}"></script>
@endpush
