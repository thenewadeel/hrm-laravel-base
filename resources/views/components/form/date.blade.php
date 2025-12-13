@props([
    'id',
    'name',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'class' => null
])

@php
    $defaultClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500';
    $finalClass = $class ? $defaultClass . ' ' . $class : $defaultClass;
@endphp

<input 
    type="date" 
    id="{{ $id }}" 
    name="{{ $name }}" 
    value="{{ $value }}"
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    class="{{ $finalClass }}"
>