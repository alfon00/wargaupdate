@php
    use App\Http\Controllers\Public\ActivityController;
    $limit = $limit ?? 3;
    $kegiatan = ActivityController::sortedKegiatan()->take($limit);
@endphp
@if($kegiatan->isNotEmpty())
<section class="lw-services-section lw-kegiatan-section" id="kegiatan" aria-labelledby="home-kegiatan-heading" tabindex="-1">
    <div class="lw-services-admin-intro">
        <h2 id="home-kegiatan-heading" class="lw-section-title lw-mb-4">Kegiatan RT</h2>
        <div class="lw-catalog-grid lw-kegiatan-grid">
            @foreach($kegiatan as $item)
                @include('public.partials.kegiatan-card', ['item' => $item])
            @endforeach
        </div>
    </div>
</section>
@endif
