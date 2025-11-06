{{-- resources/views/components/button/secondary.blade.php --}}
@props(['size' => 'md', 'type' => 'button', 'href' => null])

@if (isset($href))
    <a href="{{ $href }}"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-md font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2']) }}>
        <x-button :variant="'primary'" :size="$size" :type="$type">
            {{ $slot }}
        </x-button>
    </a>
@else
    <x-button :variant="'primary'" :size="$size" :type="$type" {{ $attributes }}>
        {{ $slot }}
    </x-button>
@endif
