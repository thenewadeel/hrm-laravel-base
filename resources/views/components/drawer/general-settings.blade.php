<div class="space-y-4">
    <h3 class="text-sm font-semibold text-primary">General Settings</h3>
    
    {{-- Application Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Application</h4>
        
        <div>
            <label class="text-sm text-secondary">Application Name</label>
            <input type="text" value="{{ config('app.name') }}" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>

        <div>
            <label class="text-sm text-secondary">Default Language</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>English</option>
                <option>Spanish</option>
                <option>French</option>
                <option>German</option>
            </select>
        </div>

        <div>
            <label class="text-sm text-secondary">Timezone</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>UTC</option>
                <option>America/New_York</option>
                <option>America/Los_Angeles</option>
                <option>Europe/London</option>
                <option>Asia/Tokyo</option>
            </select>
        </div>
    </div>

    {{-- Display Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Display</h4>
        
        <div>
            <label class="text-sm text-secondary">Theme</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>Light</option>
                <option>Dark</option>
                <option>System</option>
            </select>
        </div>

        <div>
            <label class="text-sm text-secondary">Date Format</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>MM/DD/YYYY</option>
                <option>DD/MM/YYYY</option>
                <option>YYYY-MM-DD</option>
            </select>
        </div>

        <div>
            <label class="text-sm text-secondary">Time Format</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>12-hour</option>
                <option>24-hour</option>
            </select>
        </div>
    </div>

    {{-- Notification Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Notifications</h4>
        
        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Email Notifications</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                    role="switch"
                    aria-checked="true">
                <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>

        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Browser Notifications</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                    role="switch"
                    aria-checked="true">
                <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>

        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Sound Alerts</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-secondary transition-colors"
                    role="switch"
                    aria-checked="false">
                <span class="translate-x-1 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>
    </div>
</div>