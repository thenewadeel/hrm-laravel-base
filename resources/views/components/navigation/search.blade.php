{{-- Search Bar --}}
@props([
    'placeholder' => 'Search...',
    'action' => null,
    'method' => 'GET',
])

<form action="{{ $action }}" method="{{ $method }}" class="w-full">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <x-heroicon-o-magnifying-glass class="h-5 w-5 text-muted" />
        </div>
        
        <input type="text" 
               name="search" 
               placeholder="{{ $placeholder }}" 
                class="block w-full pl-10 pr-3 py-2 border border-primary rounded-md leading-5 surface placeholder-muted focus:outline-none focus:placeholder-muted focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
    </div>
</form>