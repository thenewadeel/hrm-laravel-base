<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body x-data="{
        test: 'working',
        drawers: {
            left: false,
            right: false,
            top: false,
            bottom: false
        },
        toggleDrawer(position) {
            const newState = !this.drawers[position];
            this.closeAllDrawers();
            this.drawers[position] = newState;
            console.log(`Drawer ${position} is now: ${newState ? 'OPEN' : 'CLOSED'}`);
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
    class="font-sans antialiased bg-primary text-primary">
    
    <div class="fixed top-4 right-4 bg-red-500 text-white p-2 rounded z-50">
        Test: <span x-text="test"></span>
    </div>

    <div class="drawer-container relative min-h-screen" role="application">
        <header class="surface shadow-sm border-b border-secondary sticky top-0 z-30">
            <div class="px-4 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <button @click="toggleDrawer('left')" 
                            class="p-2 rounded-md hover:bg-secondary text-secondary hover:text-primary transition-colors">
                        <x-heroicon-o-bars-3 class="w-6 h-6" />
                    </button>
                    <h1 class="text-xl font-semibold">{{ config('app.name') }}</h1>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button @click="toggleDrawer('right')" 
                            class="p-2 rounded-md hover:bg-secondary text-secondary hover:text-primary transition-colors">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-1">
            @yield('content')
        </main>
        
        <x-drawer position="left" title="Navigation" width="320px">
            <div class="p-4">
                <h3 class="text-lg font-semibold mb-4">Navigation</h3>
                <p class="text-secondary">Simple navigation content</p>
            </div>
        </x-drawer>
        
        <x-drawer position="right" title="Settings" width="360px">
            <div class="p-4">
                <h3 class="text-lg font-semibold mb-4">Settings</h3>
                <p class="text-secondary">Simple settings content</p>
            </div>
        </x-drawer>
        
        <x-drawer.overlay />
    </div>

    @livewireScripts
</body>

</html>