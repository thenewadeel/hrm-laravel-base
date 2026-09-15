{{-- Notification Bell --}}
@props([
    'count' => 0,
    'showCount' => true,
])

<div class="relative">
    <button class="p-1 rounded-full text-muted hover:text-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <span class="sr-only">View notifications</span>
        
        <!-- Bell Icon -->
        <x-heroicon-o-bell class="h-6 w-6" />
        
        <!-- Notification Count Badge -->
        @if($showCount && $count > 0)
            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-surface"></span>
        @endif
    </button>
</div>