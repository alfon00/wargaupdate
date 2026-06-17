@props([
    'href',
    'title',
    'description',
    'badge' => null,
])

<x-panel.quick-card :href="$href" :title="$title" :description="$description" :badge="$badge" {{ $attributes }} />
