@props([
    'actionUrl' => null,
    'buttonUrl' => null,
    'buttonLabel' => null,
])

<x-panel.page-toolbar :action-url="$actionUrl" :button-url="$buttonUrl" :button-label="$buttonLabel" {{ $attributes }}>
    {{ $slot }}
</x-panel.page-toolbar>
