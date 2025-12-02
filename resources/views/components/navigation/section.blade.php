@props([
    'title' => '',
    'icon' => null,
])

<div class="px-4 py-2">
    <div class="font-medium text-xs text-muted uppercase tracking-wider mb-2">
        {{ $title }}
    </div>
    
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>