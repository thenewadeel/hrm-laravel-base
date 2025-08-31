{{-- resources/views/components/button/ghost.blade.php --}}
@props(['size' => 'md', 'type' => 'button'])
<x-button :variant="'ghost'" :size="$size" :type="$type" {{ $attributes }}>
    {{ $slot }}
</x-button>
