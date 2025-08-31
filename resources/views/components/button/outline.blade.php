{{-- resources/views/components/button/outline.blade.php --}}
@props(['size' => 'md', 'type' => 'button'])
<x-button :variant="'outline'" :size="$size" :type="$type" {{ $attributes }}>
    {{ $slot }}
</x-button>
