{{-- Floating Action Buttons for Mobile Drawer Access --}}
<div class="fixed bottom-4 right-4 z-30 flex flex-col space-y-2 sm:hidden">
    {{-- Main FAB --}}
    <div x-data="{ expanded: false }" class="relative">
        <button @click="expanded = !expanded"
                class="w-14 h-14 bg-primary text-primary-contrast rounded-full shadow-lg flex items-center justify-center transition-all hover:scale-110"
                :class="{ 'rotate-45': expanded }">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
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
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </x-drawer.toggle>
            
            {{-- Settings --}}
            <x-drawer.toggle target="drawer-right" label="Toggle module settings" class="w-12 h-12 bg-secondary text-primary rounded-full shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </x-drawer.toggle>
            
            {{-- User Preferences --}}
            <x-drawer.toggle target="drawer-bottom" label="Toggle user preferences" class="w-12 h-12 bg-secondary text-primary rounded-full shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </x-drawer.toggle>
        </div>
    </div>
</div>