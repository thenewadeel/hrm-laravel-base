{{-- Notification Bell --}}
@props([
    'count' => 0,
    'showCount' => true,
])

<div class="relative">
    <button class="p-1 rounded-full text-muted hover:text-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <span class="sr-only">View notifications</span>
        
        <!-- Bell Icon -->
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118.627 5.63c0-1.102-.897-2-2-2s-2 .897-2 2c0 .732.381 1.636 1.419 2.23l.374 1.486A2.032 2.032 0 0119.627 18.37c-1.103 0-2-.897-2-2s-.897 2-2 2c0 .732.381 1.636 1.419 2.23l.374 1.486A2.032 2.032 0 0118.627 18.37zM3 12a3 3 0 100-6 3 3 0 100 6zm0 0a3 3 0 100-6 3 3 0 100 6zm12 0a3 3 0 100-6 3 3 0 100 6z" />
        </svg>
        
        <!-- Notification Count Badge -->
        @if($showCount && $count > 0)
            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-surface"></span>
        @endif
    </button>
</div>