@if(! empty($lurah['visi']) || ! empty($lurah['misi']))
    <dl class="lw-profile-detail-vision lw-profile-detail-vision--summary">
        @if(! empty($lurah['visi']))
            <div>
                <dt>Visi</dt>
                <dd class="lw-profile-vision-text">{{ $lurah['visi'] }}</dd>
            </div>
        @endif
        @if(! empty($lurah['misi']))
            <div>
                <dt>Misi</dt>
                <dd>
                    @include('public.partials.profile-vision-misi-body', ['text' => $lurah['misi']])
                </dd>
            </div>
        @endif
    </dl>
@endif
