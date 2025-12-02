{{-- Enhanced Theme Toggle --}}
<div x-data="{ 
    darkMode: localStorage.getItem('theme') === 'dark' || 
              (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) 
}" 
     x-init="$watch('darkMode', value => {
         localStorage.setItem('theme', value ? 'dark' : 'light');
         document.documentElement.classList.toggle('dark', value);
         $dispatch('theme-changed', { theme: value ? 'dark' : 'light' });
     })"
     class="relative">
     
    <button type="button" 
            @click="darkMode = !darkMode"
            class="p-2 rounded-lg transition-all duration-300 transform hover:scale-105
                   bg-surface border border-secondary
                   hover:bg-surface-elevated
                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary
                   dark:focus:ring-offset-bg-primary"
            aria-label="Toggle dark mode">
        
        <span class="sr-only">Toggle dark mode</span>
        
        <!-- Sun Icon for light mode -->
        <svg x-show="!darkMode" 
             x-transition:enter="transition ease-in-out duration-300"
             x-transition:enter-start="opacity-0 rotate-180 scale-75"
             x-transition:enter-end="opacity-100 rotate-0 scale-100"
             x-transition:leave="transition ease-in-out duration-300"
             x-transition:leave-start="opacity-100 rotate-0 scale-100"
             x-transition:leave-end="opacity-0 rotate-180 scale-75"
             class="h-5 w-5 text-amber-500" 
             fill="none" 
             stroke="currentColor" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.708-.708M6.343 6.343l-.707-.707m6.364 6.364l-.708-.708M6.343 17.657l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        
        <!-- Moon Icon for dark mode -->
        <svg x-show="darkMode" 
             x-transition:enter="transition ease-in-out duration-300"
             x-transition:enter-start="opacity-0 rotate-180 scale-75"
             x-transition:enter-end="opacity-100 rotate-0 scale-100"
             x-transition:leave="transition ease-in-out duration-300"
             x-transition:leave-start="opacity-100 rotate-0 scale-100"
             x-transition:leave-end="opacity-0 rotate-180 scale-75"
             class="h-5 w-5 text-blue-400" 
             fill="none" 
             stroke="currentColor" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M20.354 15.354A9 9 0 0118.364 5.636 9 9 0 00-12.72 12.72zM12 18a8 8 0 00-8-8 8 8 0 008 8z" />
        </svg>
    </button>
    
    <!-- Tooltip -->
    <div x-show="!darkMode" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-text-inverse bg-gray-900 rounded whitespace-nowrap z-50">
         Switch to dark mode
        <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
    </div>
    
    <div x-show="darkMode" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs text-text-inverse bg-gray-900 rounded whitespace-nowrap z-50">
         Switch to light mode
        <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
    </div>
</div>