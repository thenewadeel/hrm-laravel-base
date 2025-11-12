@props(['status' => 'active'])

@php
    $styles = [
        'active' => 'bg-green-100 text-green-800',
        'inactive' => 'bg-gray-100 text-gray-800',
        'draft' => 'bg-yellow-100 text-yellow-800',
        'posted' => 'bg-blue-100 text-blue-800',
        'void' => 'bg-red-100 text-red-800',
        'finalized' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
    
    $labels = [
        'active' => 'Active',
        'inactive' => 'Inactive', 
        'draft' => 'Draft',
        'posted' => 'Posted',
        'void' => 'Void',
        'finalized' => 'Finalized',
        'cancelled' => 'Cancelled',
    ];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $styles[$status] ?? $styles['inactive'] }}">
    {{ $labels[$status] ?? ucfirst($status) }}
</span>