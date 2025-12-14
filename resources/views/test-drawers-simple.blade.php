@extends('components.drawer-layout')

@section('header')
    <h1 class="text-xl font-semibold">Simple Drawer Test</h1>
@endsection

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Simple Drawer Test</h2>
    <p class="mb-4">This is a simple test page to verify the drawer system works.</p>
    
    <div class="space-y-2">
        <button @click="toggleDrawer('left')" class="px-4 py-2 bg-blue-500 text-white rounded">
            Toggle Left Drawer
        </button>
        <button @click="toggleDrawer('right')" class="px-4 py-2 bg-green-500 text-white rounded">
            Toggle Right Drawer
        </button>
    </div>
</div>
@endsection