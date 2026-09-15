{{-- Floating Action Buttons for Mobile Drawer Access --}}
<div class="fixed bottom-4 right-4 z-30 flex flex-col space-y-2 sm:hidden">
    {{-- Main FAB --}}
    <div x-data="{ expanded: false }" class="relative">
        <button @click="expanded = !expanded"
                class="w-14 h-14 bg-primary text-primary-contrast rounded-full shadow-lg flex items-center justify-center transition-all hover:scale-110"
                :class="{ 'rotate-45': expanded }">
            <x-heroicon-o-plus class="w-6 h-6" />
        </button>
        
        {{-- Mini FABs --}}
        <div x-show="expanded"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-75"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-75"
             class="absolute bottom-16 right-0 flex flex-col space-y-2">
            
            {{-- Navigation --}}
            <x-drawer.toggle target="drawer-left" label="Toggle navigation menu" class="w-12 h-12 bg-secondary text-primary rounded-full shadow-md">
                <x-heroicon-o-bars-3 class="w-5 h-5" />
            </x-drawer.toggle>
            
            {{-- Settings --}}
            <x-drawer.toggle target="drawer-right" label="Toggle module settings" class="w-12 h-12 bg-secondary text-primary rounded-full shadow-md">
                <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
            </x-drawer.toggle>
            
            {{-- User Preferences --}}
            <x-drawer.toggle target="drawer-bottom" label="Toggle user preferences" class="w-12 h-12 bg-secondary text-primary rounded-full shadow-md">
                <x-heroicon-o-user class="w-5 h-5" />
            </x-drawer.toggle>
        </div>
    </div>
</div>