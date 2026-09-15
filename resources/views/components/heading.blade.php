@props([
    'title' => null,
    'description' => null,
    'align' => 'left',
    'as' => 'h1',
])

@php
    $alignClass = $align === 'center' ? 'text-center' : 'text-left';
    $sizeClass = match ($as) {
        'h1' => 'text-2xl',
        'h2' => 'text-xl',
        'h3' => 'text-lg',
        default => 'text-2xl',
    };
@endphp

<div @class(['w-full', $alignClass])>
    <{{ $as }} class="{{ $sizeClass }} font-bold tracking-tight text-primary">
        {{ $title ?? $slot }}
    </{{ $as }}>

    @if ($description)
        <p class="mt-2 text-sm text-secondary">{{ $description }}</p>
    @endif
</div>