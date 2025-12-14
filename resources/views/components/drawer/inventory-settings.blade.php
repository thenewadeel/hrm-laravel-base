<div class="space-y-4">
    <h3 class="text-sm font-semibold text-primary">Inventory Settings</h3>
    
    {{-- Stock Management --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Stock Management</h4>
        
        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Low Stock Alerts</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                    role="switch"
                    aria-checked="true">
                <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>

        <div>
            <label class="text-sm text-secondary">Reorder Point (days)</label>
            <input type="number" value="7" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>

        <div>
            <label class="text-sm text-secondary">Default Store</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>Main Store</option>
                <option>Secondary Store</option>
                <option>Warehouse A</option>
            </select>
        </div>
    </div>

    {{-- Costing Method --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Costing Method</h4>
        <select class="w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            <option>FIFO (First In, First Out)</option>
            <option>Weighted Average</option>
            <option>LIFO (Last In, First Out)</option>
        </select>
    </div>

    {{-- Display Options --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Display Options</h4>
        
        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Show Item Images</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                    role="switch"
                    aria-checked="true">
                <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>

        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Show Stock Values</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                    role="switch"
                    aria-checked="true">
                <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>
    </div>
</div>