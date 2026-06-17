@if(filled($rtProfile->visi) || filled($rtProfile->misi))
    <dl class="lw-profile-detail-vision lw-profile-detail-vision--summary">
        @if(filled($rtProfile->visi))
            <div>
                <dt>Visi</dt>
                <dd class="lw-profile-vision-text">{{ $rtProfile->visi }}</dd>
            </div>
        @endif
        @if(filled($rtProfile->misi))
            <div>
                <dt>Misi</dt>
                <dd>
                    @include('public.partials.profile-vision-misi-body', ['text' => $rtProfile->misi])
                </dd>
            </div>
        @endif
    </dl>
@endif
