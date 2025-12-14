<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Drawer Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body x-data="{
    drawers: {
        left: false,
        right: false,
        top: false,
        bottom: false
    },
    toggleDrawer(position) {
        this.drawers[position] = !this.drawers[position];
    },
    closeDrawer(position) {
        this.drawers[position] = false;
    }
}" class="font-sans antialiased bg-primary text-primary">
    
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Minimal Drawer Test</h1>
        
        <div class="space-x-4">
            <button @click="toggleDrawer('left')" class="px-4 py-2 bg-blue-500 text-white rounded">
                Toggle Left
            </button>
            <button @click="toggleDrawer('right')" class="px-4 py-2 bg-green-500 text-white rounded">
                Toggle Right
            </button>
        </div>
    </div>
    
    {{-- Simple Left Drawer --}}
    <div x-show="drawers.left"
         x-transition:enter="transition ease-in-out duration-300"
         x-transition:enter-start="transform -translate-x-full opacity-0"
         x-transition:enter-end="transform translate-0 opacity-100"
         x-transition:leave="transition ease-in-out duration-200"
         x-transition:leave-start="transform translate-0 opacity-100"
         x-transition:leave-end="transform -translate-x-full opacity-0"
         class="fixed left-0 top-0 h-full w-80 bg-surface border-r border-secondary z-50"
         x-cloak>
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-4">Left Drawer</h2>
            <button @click="closeDrawer('left')" class="px-4 py-2 bg-red-500 text-white rounded">
                Close
            </button>
        </div>
    </div>
    
    {{-- Simple Right Drawer --}}
    <div x-show="drawers.right"
         x-transition:enter="transition ease-in-out duration-300"
         x-transition:enter-start="transform translate-x-full opacity-0"
         x-transition:enter-end="transform translate-0 opacity-100"
         x-transition:leave="transition ease-in-out duration-200"
         x-transition:leave-start="transform translate-0 opacity-100"
         x-transition:leave-end="transform translate-x-full opacity-0"
         class="fixed right-0 top-0 h-full w-80 bg-surface border-l border-secondary z-50"
         x-cloak>
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-4">Right Drawer</h2>
            <button @click="closeDrawer('right')" class="px-4 py-2 bg-red-500 text-white rounded">
                Close
            </button>
        </div>
    </div>
    
    @livewireScripts
</body>
</html>