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
            data-theme-toggle
            class="p-2 rounded-lg transition-all duration-300 transform hover:scale-105
                   bg-surface border border-secondary
                   hover:bg-surface-elevated
                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary
                   dark:focus:ring-offset-bg-primary"
            aria-label="Toggle theme">
        
        <span class="sr-only">Toggle theme</span>
        
        <!-- Sun Icon for light mode -->
        <x-heroicon-o-sun x-show="!darkMode"
             x-transition:enter="transition ease-in-out duration-300"
             x-transition:enter-start="opacity-0 rotate-180 scale-75"
             x-transition:enter-end="opacity-100 rotate-0 scale-100"
             x-transition:leave="transition ease-in-out duration-300"
             x-transition:leave-start="opacity-100 rotate-0 scale-100"
             x-transition:leave-end="opacity-0 rotate-180 scale-75"
             class="h-5 w-5 text-amber-500" />

        <!-- Moon Icon for dark mode -->
        <x-heroicon-o-moon x-show="darkMode"
             x-transition:enter="transition ease-in-out duration-300"
             x-transition:enter-start="opacity-0 rotate-180 scale-75"
             x-transition:enter-end="opacity-100 rotate-0 scale-100"
             x-transition:leave="transition ease-in-out duration-300"
             x-transition:leave-start="opacity-100 rotate-0 scale-100"
             x-transition:leave-end="opacity-0 rotate-180 scale-75"
             class="h-5 w-5 text-blue-400" />
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