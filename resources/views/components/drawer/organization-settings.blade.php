<div class="space-y-4">
    <h3 class="text-sm font-semibold text-primary">Organization Settings</h3>
    
    {{-- General Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">General</h4>
        
        <div>
            <label class="text-sm text-secondary">Organization Name</label>
            <input type="text" value="{{ config('app.name') }}" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>

        <div>
            <label class="text-sm text-secondary">Business Email</label>
            <input type="email" value="contact@company.com" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>

        <div>
            <label class="text-sm text-secondary">Phone Number</label>
            <input type="tel" value="+1 234 567 8900" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>
    </div>

    {{-- Member Management --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Member Management</h4>
        
        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Require Admin Approval</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                    role="switch"
                    aria-checked="true">
                <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>

        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Allow Member Registration</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-secondary transition-colors"
                    role="switch"
                    aria-checked="false">
                <span class="translate-x-1 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>

        <div>
            <label class="text-sm text-secondary">Default Member Role</label>
            <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>Member</option>
                <option>Manager</option>
                <option>Viewer</option>
            </select>
        </div>
    </div>

    {{-- Security Settings --}}
    <div class="space-y-3">
        <h4 class="text-sm font-medium text-secondary">Security</h4>
        
        <div>
            <label class="text-sm text-secondary">Session Timeout (minutes)</label>
            <input type="number" value="120" min="15" max="480" class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
        </div>

        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Two-Factor Authentication</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-secondary transition-colors"
                    role="switch"
                    aria-checked="false">
                <span class="translate-x-1 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>

        <div class="flex items-center justify-between">
            <label class="text-sm text-secondary">Password Complexity</label>
            <button type="button" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                    role="switch"
                    aria-checked="true">
                <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
            </button>
        </div>
    </div>
</div>