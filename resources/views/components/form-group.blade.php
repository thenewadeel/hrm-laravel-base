@props([
    'label' => null,
    'for' => null,
    'required' => false,
    'class' => null
])

@php
    $defaultClass = 'mb-4';
    $finalClass = $class ? $defaultClass . ' ' . $class : $defaultClass;
@endphp

<div class="{{ $finalClass }}">
    @if($label)
        <x-form.label :for="$for" :required="$required">
            {{ $label }}
        </x-form.label>
    @endif
    
    {{ $slot }}
</div>