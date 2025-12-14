@props([
    'mainContent' => null,
    'drawerSlots' => null
])

<div x-data="{
    drawers: {
        left: false,
        right: false,
        top: false,
        bottom: false
    },
    drawerState(position) {
        return this.drawers[position] || false;
    },
    toggleDrawer(position) {
        const newState = !this.drawers[position];
        this.closeAllDrawers();
        this.drawers[position] = newState;
    },
    closeDrawer(position) {
        this.drawers[position] = false;
    },
    closeAllDrawers() {
        Object.keys(this.drawers).forEach(key => {
            this.drawers[key] = false;
        });
    },
    handleKeydown(e) {
        if (e.key === 'Escape') {
            this.closeAllDrawers();
        }
    }
}" 
@keydown.window="handleKeydown($event)"
class="drawer-container relative min-h-screen" role="application">
    {{-- Overlay for mobile and backdrop --}}
    <div x-show="Object.values(drawers).some(v => v)"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeAllDrawers()"
         class="drawer-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
         aria-hidden="true">
    </div>
    
    {{-- Main content area --}}
    <main class="main-content">
        {{ $mainContent ?? $slot }}
    </main>
    
    {{-- Drawer slots will be rendered here --}}
    {{ $drawerSlots }}
</div>