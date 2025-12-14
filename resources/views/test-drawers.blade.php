@extends('components.drawer-layout')

@section('header')
    <h1 class="text-xl font-semibold">Drawer System Test</h1>
@endsection

@section('content')
<div class="p-6 space-y-6">
    <div class="surface rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Alpine.js Drawer System Test</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="p-4 border rounded-lg">
                <h3 class="font-semibold mb-2">Quick Test Buttons</h3>
                <div class="space-y-2">
                    <button @click="toggleDrawer('left')" 
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Toggle Left Drawer
                    </button>
                    <button @click="toggleDrawer('right')" 
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                        Toggle Right Drawer
                    </button>
                    <button @click="toggleDrawer('top')" 
                            class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                        Toggle Top Drawer
                    </button>
                    <button @click="toggleDrawer('bottom')" 
                            class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                        Toggle Bottom Drawer
                    </button>
                    <button @click="closeAllDrawers()" 
                            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                        Close All Drawers
                    </button>
                </div>
            </div>
            
            <div class="p-4 border rounded-lg">
                <h3 class="font-semibold mb-2">Drawer States</h3>
                <div class="space-y-1 font-mono text-sm">
                    <div>Left: <span x-text="drawers.left ? 'OPEN' : 'CLOSED'" :class="drawers.left ? 'text-green-600' : 'text-gray-600'"></span></div>
                    <div>Right: <span x-text="drawers.right ? 'OPEN' : 'CLOSED'" :class="drawers.right ? 'text-green-600' : 'text-gray-600'"></span></div>
                    <div>Top: <span x-text="drawers.top ? 'OPEN' : 'CLOSED'" :class="drawers.top ? 'text-green-600' : 'text-gray-600'"></span></div>
                    <div>Bottom: <span x-text="drawers.bottom ? 'OPEN' : 'CLOSED'" :class="drawers.bottom ? 'text-green-600' : 'text-gray-600'"></span></div>
                </div>
            </div>
        </div>
        
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="font-semibold mb-2">Instructions</h3>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li>Use the header buttons or quick test buttons to toggle drawers</li>
                <li>Press ESC to close all drawers</li>
                <li>Click the overlay to close the active drawer</li>
                <li>Check the console for debugging information</li>
                <li>Each drawer should have smooth transitions without conflicts</li>
            </ul>
        </div>
        
        <div class="mt-8 p-6 bg-gray-50 rounded-lg">
            <h3 class="font-semibold mb-4">Content Area</h3>
            <p class="text-gray-600 mb-4">
                This is the main content area. Drawers should appear over this content without affecting layout.
                The drawer system now uses Alpine.js transitions exclusively, eliminating all CSS transform conflicts.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-white rounded border">Card 1</div>
                <div class="p-4 bg-white rounded border">Card 2</div>
                <div class="p-4 bg-white rounded border">Card 3</div>
            </div>
        </div>
    </div>
</div>
@endsection