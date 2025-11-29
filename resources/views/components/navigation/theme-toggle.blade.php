{{-- Theme Toggle --}}
@props([
    'darkMode' => false,
])

<div>
    <button type="button" 
            class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition-colors duration-200"
            @click="$dispatch('toggle-theme')"
            aria-label="Toggle dark mode">
        <span class="sr-only">Toggle dark mode</span>
        
        <!-- Sun Icon for light mode -->
        <svg x-show="!{{ $darkMode ? 'true' : 'false' }}" 
             class="h-5 w-5" 
             fill="none" 
             stroke="currentColor" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.708-.708M6.343 6.343l-.707-.707m6.364 6.364l-.708-.708M6.343 17.657l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        
        <!-- Moon Icon for dark mode -->
        <svg x-show="{{ $darkMode ? 'true' : 'false' }}" 
             class="h-5 w-5" 
             fill="none" 
             stroke="currentColor" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 0118.364 5.636 9 9 0 00-12.72 12.72zM12 18a8 8 0 00-8-8 8 8 0 008 8z" />
        </svg>
    </button>
</div>