<x-panel.page-head
    :title="$title"
    :eyebrow="$eyebrow ?? 'Panel Kelurahan'"
    :lead="$lead ?? null"
>
    @isset($actions)
        <x-slot:actions>{!! $actions !!}</x-slot:actions>
    @endisset
</x-panel.page-head>
