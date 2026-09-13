@props([
    'tags',
    'color' => 'blue',
    'size' => 'sm',
    'variant' => 'outline',
    'removable' => false,
    'limit' => null,
    'showMore' => true,
])

@php
    $displayTags = $limit ? collect($tags)->take($limit) : collect($tags);
    $remainingCount = $limit ? collect($tags)->count() - $limit : 0;
@endphp

<div class="flex flex-wrap gap-1" {{ $attributes->except('tags') }}>
    @foreach($displayTags as $index => $tag)
        @if(is_string($tag))
            @php $tag = ['label' => $tag, 'value' => $tag]; @endphp
        @endif
        
        @if($removable)
            <x-badge
                :color="$color"
                :variant="$variant"
                :size="$size"
                dismissible
                wire:key="tag-{{ $tag['value'] ?? $index }}"
                x-on:dismiss="$wire.call('removeTag', '{{ $tag['value'] ?? $tag['label'] }}')"
            >
                {{ $tag['label'] }}
            </x-badge>
        @else
            <x-badge
                :color="$color"
                :variant="$variant"
                :size="$size"
                wire:key="tag-{{ $tag['value'] ?? $index }}"
            >
                {{ $tag['label'] }}
            </x-badge>
        @endif
    @endforeach
    
    @if($limit && $remainingCount > 0 && $showMore)
        <x-badge
            color="gray"
            variant="subtle"
            :size="$size"
        >
            +{{ $remainingCount }} more
        </x-badge>
    @endif
</div>