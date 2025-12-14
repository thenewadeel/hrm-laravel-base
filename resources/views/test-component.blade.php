<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body x-data="{
    drawers: {
        left: false,
        right: false
    },
    toggleDrawer(position) {
        this.drawers[position] = !this.drawers[position];
    },
    closeDrawer(position) {
        this.drawers[position] = false;
    }
}" class="font-sans antialiased bg-primary text-primary">
    
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Component Drawer Test</h1>
        
        <div class="space-x-4">
            <button @click="toggleDrawer('left')" class="px-4 py-2 bg-blue-500 text-white rounded">
                Toggle Left
            </button>
        </div>
    </div>
    
    {{-- Test drawer component --}}
    <x-drawer position="left" title="Test Drawer" width="320px">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-4">Test Content</h2>
            <p>This is test content inside the drawer component.</p>
            <button @click="closeDrawer('left')" class="px-4 py-2 bg-red-500 text-white rounded mt-4">
                Close
            </button>
        </div>
    </x-drawer>
    
    @livewireScripts
</body>
</html>