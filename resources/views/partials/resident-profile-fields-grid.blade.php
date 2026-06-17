@php
    use App\Support\ResidentProfileDisplay;

    $fields = $fields ?? ResidentProfileDisplay::fromApplication($application);
    $variant = $variant ?? 'detail-grid';
    $wideKeys = $wideKeys ?? ['alamat'];
    $monoKeys = $monoKeys ?? ['nik'];
@endphp

@if($variant === 'dl')
    <dl class="lw-panel-dl lw-panel-dl--reference">
        @foreach(ResidentProfileDisplay::standardFields() as $field)
            @php
                $key = $field['key'];
                $value = $fields[$key] ?? '—';
            @endphp
            <div class="lw-panel-dl-row">
                <dt>{{ $field['label'] }}</dt>
                <dd @class(['lw-rt-application-detail__field-value--mono' => in_array($key, $monoKeys, true)])>{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
@else
    <div class="lw-rt-application-detail__fields">
        @foreach(ResidentProfileDisplay::standardFields() as $field)
            @php
                $key = $field['key'];
                $value = $fields[$key] ?? '—';
            @endphp
            <div @class([
                'lw-rt-application-detail__field',
                'lw-rt-application-detail__field--wide' => in_array($key, $wideKeys, true),
            ])>
                <span class="lw-rt-application-detail__field-label">{{ $field['label'] }}</span>
                <span @class([
                    'lw-rt-application-detail__field-value',
                    'lw-rt-application-detail__field-value--mono' => in_array($key, $monoKeys, true),
                ])>{{ $value }}</span>
            </div>
        @endforeach
    </div>
@endif
