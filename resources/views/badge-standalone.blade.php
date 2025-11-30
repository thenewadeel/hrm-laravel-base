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
                <x-new-badge>Default Badge</x-new-badge>
                <x-new-badge color="green">Green Badge</x-new-badge>
                <x-new-badge color="red">Red Badge</x-new-badge>
                <x-new-badge color="blue" variant="outline">Blue Outline</x-new-badge>
                <x-new-badge color="yellow" variant="subtle">Yellow Subtle</x-new-badge>
            </div>
            
            <h2 class="text-xl font-semibold mb-4">Status Badges</h2>
            <div class="flex gap-2">
                <x-new-status-badge status="active" />
                <x-new-status-badge status="inactive" />
                <x-new-status-badge status="pending" />
                <x-new-status-badge status="error" />
            </div>
            
            <h2 class="text-xl font-semibold mb-4">Category Badges</h2>
            <div class="flex gap-2">
                <x-new-category-badge category="sales" />
                <x-new-category-badge category="purchase" />
                <x-new-category-badge category="high" />
            </div>
            
            <h2 class="text-xl font-semibold mb-4">Priority Badges</h2>
            <div class="flex gap-2">
                <x-new-priority-badge priority="low" />
                <x-new-priority-badge priority="medium" />
                <x-new-priority-badge priority="high" />
                <x-new-priority-badge priority="critical" />
            </div>
        </div>
    </div>
</body>
</html>