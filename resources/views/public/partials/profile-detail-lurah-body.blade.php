<dl class="lw-profile-detail-dl">
    @if(! empty($lurah['telepon']))
        <div>
            <dt>Telepon</dt>
            <dd><a href="tel:{{ preg_replace('/\s+/', '', $lurah['telepon']) }}" class="lw-profile-phone-link">{{ $lurah['telepon'] }}</a></dd>
        </div>
    @endif
    @if(! empty($lurah['whatsapp']))
        <div>
            <dt>WhatsApp</dt>
            <dd><a href="https://wa.me/{{ preg_replace('/\D/', '', $lurah['whatsapp']) }}" class="lw-profile-phone-link" rel="noopener noreferrer">{{ $lurah['whatsapp'] }}</a></dd>
        </div>
    @endif
    @if(! empty($lurah['email']))
        <div>
            <dt>Email</dt>
            <dd><a href="mailto:{{ $lurah['email'] }}" class="lw-profile-phone-link">{{ $lurah['email'] }}</a></dd>
        </div>
    @endif
    @if(! empty($lurah['jam_layanan']))
        <div>
            <dt>Jam layanan</dt>
            <dd>{{ $lurah['jam_layanan'] }}</dd>
        </div>
    @endif
    @if(! empty($lurah['alamat_kantor']))
        <div>
            <dt>Alamat kantor kelurahan</dt>
            <dd>{{ $lurah['alamat_kantor'] }}</dd>
        </div>
    @endif
</dl>
