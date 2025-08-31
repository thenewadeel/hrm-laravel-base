{{-- resources/views/components/button/danger.blade.php --}}
@props(['size' => 'md', 'type' => 'button'])
<x-button :variant="'danger'" :size="$size" :type="$type" {{ $attributes }}>
    {{ $slot }}
</x-button>
