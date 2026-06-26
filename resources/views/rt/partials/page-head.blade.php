<x-panel.page-head :title="$title">
    @isset($actions)
        <x-slot:actions>{!! $actions !!}</x-slot:actions>
    @endisset
</x-panel.page-head>
