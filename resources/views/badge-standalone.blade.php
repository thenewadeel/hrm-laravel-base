<!DOCTYPE html>
<html>
<head>
    <title>Badge Test</title>
</head>
<body>
    <div class="p-8">
        <h1 class="text-2xl font-bold mb-4">Badge System Test</h1>
        
        <div class="space-y-4">
            <h2 class="text-xl font-semibold mb-4">Basic Badges</h2>
            <div class="flex gap-2">
                <x-badge>Default Badge</x-badge>
                <x-badge color="green">Green Badge</x-badge>
                <x-badge color="red">Red Badge</x-badge>
                <x-badge color="blue" variant="outline">Blue Outline</x-badge>
                <x-badge color="yellow" variant="subtle">Yellow Subtle</x-badge>
            </div>
            
            <h2 class="text-xl font-semibold mb-4">Status Badges</h2>
            <div class="flex gap-2">
                <x-badge status="active">Active</x-badge>
                <x-badge status="inactive">Inactive</x-badge>
                <x-badge status="pending">Pending</x-badge>
                <x-badge status="error">Error</x-badge>
            </div>
            
            <h2 class="text-xl font-semibold mb-4">Category Badges</h2>
            <div class="flex gap-2">
                <x-badge color="blue">Sales</x-badge>
                <x-badge color="green">Purchase</x-badge>
                <x-badge color="orange">High</x-badge>
            </div>
            
            <h2 class="text-xl font-semibold mb-4">Priority Badges</h2>
            <div class="flex gap-2">
                <x-badge color="blue">Low</x-badge>
                <x-badge color="yellow">Medium</x-badge>
                <x-badge color="orange">High</x-badge>
                <x-badge color="red">Critical</x-badge>
            </div>
        </div>
    </div>
</body>
</html>