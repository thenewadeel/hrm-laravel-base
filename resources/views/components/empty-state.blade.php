@props([
    'icon' => 'document',
    'title' => 'No data found',
    'description' => 'There are no items to display at this time.',
    'action' => null,
    'actionText' => 'Create New',
])

@php
    $icons = [
        'document' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
        'user' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />',
        'chart' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
        'search' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center']) }}>
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        {!! $icons[$icon] ?? $icons['document'] !!}
    </svg>
    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ $title }}</h3>
    <p class="mt-2 text-gray-500">{{ $description }}</p>
    @if ($action)
        <div class="mt-6">
            <button wire:click="{{ $action }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                {{ $actionText }}
            </button>
        </div>
    @endif
</div>
