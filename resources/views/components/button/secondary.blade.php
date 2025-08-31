{{-- resources/views/components/button/secondary.blade.php --}}
@props(['size' => 'md', 'type' => 'button'])
<x-button :variant="'secondary'" :size="$size" :type="$type" {{ $attributes }}>
    {{ $slot }}
</x-button>
