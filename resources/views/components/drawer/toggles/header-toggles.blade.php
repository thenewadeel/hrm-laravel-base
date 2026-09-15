{{-- Header Drawer Toggles --}}
<div class="flex items-center space-x-2">
    {{-- Left drawer toggle (navigation) --}}
    <x-drawer.toggle target="drawer-left" label="Toggle navigation menu" class="p-2">
        <x-heroicon-o-bars-3 class="w-6 h-6" />
    </x-drawer.toggle>
    
    {{-- Top drawer toggle (app info) - Hidden on mobile --}}
    <x-drawer.toggle target="drawer-top" label="Toggle app information" class="hidden sm:flex p-2">
        <x-heroicon-o-information-circle class="w-5 h-5" />
    </x-drawer.toggle>
    
    {{-- Right drawer toggle (module settings) --}}
    <x-drawer.toggle target="drawer-right" label="Toggle module settings" class="p-2">
        <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
    </x-drawer.toggle>
    
    {{-- Bottom drawer toggle (user preferences) --}}
    <x-drawer.toggle target="drawer-bottom" label="Toggle user preferences" class="p-2">
        <x-heroicon-o-user class="w-5 h-5" />
    </x-drawer.toggle>
</div>