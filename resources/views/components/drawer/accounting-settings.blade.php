<div class="space-y-4">
    <h3 class="text-sm font-semibold text-primary">Accounting Settings</h3>
    
    {{-- Financial Year --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Financial Year</h4>
        
        <div>
            <label class="text-sm text-secondary">Current Financial Year</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>2024-2025</option>
                <option>2025-2026</option>
                <option>2023-2024</option>
            </select>
        </div>

        <div>
            <label class="text-sm text-secondary">Year Start Date</label>
            <input type="date" value="2024-01-01" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>
    </div>

    {{-- Voucher Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Voucher Settings</h4>
        
        <div>
            <label class="text-sm text-secondary">Voucher Number Prefix</label>
            <input type="text" value="VOU-" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>

        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Auto-approve Vouchers</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-secondary transition-colors"
                    role="switch"
                    aria-checked="false">
                <span class="translate-x-1 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>
    </div>

    {{-- Currency Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Currency Settings</h4>
        
        <div>
            <label class="text-sm text-secondary">Base Currency</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>USD - US Dollar</option>
                <option>EUR - Euro</option>
                <option>GBP - British Pound</option>
                <option>INR - Indian Rupee</option>
            </select>
        </div>

        <div>
            <label class="text-sm text-secondary">Decimal Places</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>2</option>
                <option>3</option>
                <option>4</option>
            </select>
        </div>
    </div>

    {{-- Tax Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Tax Settings</h4>
        
        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Enable Tax Calculation</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                    role="switch"
                    aria-checked="true">
                <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>

        <div>
            <label class="text-sm text-secondary">Default Tax Rate (%)</label>
            <input type="number" value="18" step="0.1" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>
    </div>
</div>