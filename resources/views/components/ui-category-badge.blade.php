@props(['category' => 'default'])

@php
    $color = $getColor();

    $colorClasses = [
        'green' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'purple' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
        'pink' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
        'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'gray' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.($colorClasses[$color] ?? $colorClasses['gray'])]) }}>
    {{ $getLabel() }}
</span>