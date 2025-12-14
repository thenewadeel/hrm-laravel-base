<div class="space-y-4">
    <h3 class="text-sm font-semibold text-primary">HR Settings</h3>
    
    {{-- Attendance Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Attendance Settings</h4>
        
        <div>
            <label class="text-sm text-secondary">Working Hours</label>
            <div class="mt-1 grid grid-cols-2 gap-2">
                <input type="time" value="09:00" class="px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Start">
                <input type="time" value="17:00" class="px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary" placeholder="End">
            </div>
        </div>

        <div>
            <label class="text-sm text-secondary">Working Days</label>
            <div class="mt-1 space-y-1">
                <label class="flex items-center text-sm">
                    <input type="checkbox" checked class="mr-2 rounded border-secondary text-primary focus:ring-primary">
                    Monday
                </label>
                <label class="flex items-center text-sm">
                    <input type="checkbox" checked class="mr-2 rounded border-secondary text-primary focus:ring-primary">
                    Tuesday
                </label>
                <label class="flex items-center text-sm">
                    <input type="checkbox" checked class="mr-2 rounded border-secondary text-primary focus:ring-primary">
                    Wednesday
                </label>
                <label class="flex items-center text-sm">
                    <input type="checkbox" checked class="mr-2 rounded border-secondary text-primary focus:ring-primary">
                    Thursday
                </label>
                <label class="flex items-center text-sm">
                    <input type="checkbox" checked class="mr-2 rounded border-secondary text-primary focus:ring-primary">
                    Friday
                </label>
                <label class="flex items-center text-sm">
                    <input type="checkbox" class="mr-2 rounded border-secondary text-primary focus:ring-primary">
                    Saturday
                </label>
                <label class="flex items-center text-sm">
                    <input type="checkbox" class="mr-2 rounded border-secondary text-primary focus:ring-primary">
                    Sunday
                </label>
            </div>
        </div>
    </div>

    {{-- Leave Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Leave Settings</h4>
        
        <div>
            <label class="text-sm text-secondary">Annual Leave Days</label>
            <input type="number" value="21" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>

        <div>
            <label class="text-sm text-secondary">Sick Leave Days</label>
            <input type="number" value="10" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>

        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Require Approval for Leave</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                    role="switch"
                    aria-checked="true">
                <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>
    </div>

    {{-- Payroll Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Payroll Settings</h4>
        
        <div>
            <label class="text-sm text-secondary">Payroll Frequency</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>Monthly</option>
                <option>Bi-weekly</option>
                <option>Weekly</option>
            </select>
        </div>

        <div>
            <label class="text-sm text-secondary">Payroll Processing Day</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>Last day of month</option>
                <option>25th of month</option>
                <option>1st of next month</option>
            </select>
        </div>
    </div>
</div>