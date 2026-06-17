@props([
    'title' => 'Belum ada data',
    'description' => null,
    'actionUrl' => null,
    'actionLabel' => null,
])

<x-panel.empty-state :title="$title" :description="$description" :action-url="$actionUrl" :action-label="$actionLabel" {{ $attributes }} />
