@php
    $currentRoute = request()->route() ? request()->route()->getName() : 'dashboard';
    $currentModule = explode('.', $currentRoute)[0] ?? 'dashboard';
    
    // Determine which settings component to show
    $settingsComponent = match($currentModule) {
        'inventory' => 'x-drawer.inventory-settings',
        'accounts' => 'x-drawer.accounting-settings',
        'hrm' => 'x-drawer.hr-settings',
        'organization' => 'x-drawer.organization-settings',
        default => 'x-drawer.general-settings'
    };
@endphp

<div class="p-4 space-y-6">
    {{-- Module Context --}}
    <div class="bg-secondary/10 rounded-lg p-3">
        <div class="flex items-center space-x-2">
            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
            <span class="text-sm font-medium text-primary">Current Module:</span>
            <span class="text-sm text-secondary capitalize">{{ $currentModule }}</span>
        </div>
    </div>

    {{-- Dynamic Settings Content --}}
    <div class="settings-content">
        @if($currentModule === 'inventory')
            <x-drawer.inventory-settings />
        @elseif($currentModule === 'accounts')
            <x-drawer.accounting-settings />
        @elseif($currentModule === 'hrm')
            <x-drawer.hr-settings />
        @elseif($currentModule === 'organization')
            <x-drawer.organization-settings />
        @else
            <x-drawer.general-settings />
        @endif
    </div>

    {{-- Common Settings --}}
    <div class="border-t border-secondary pt-4">
        <h3 class="text-sm font-semibold text-primary mb-3">Common Settings</h3>
        <div class="space-y-3">
            {{-- Display Density --}}
            <div>
                <label class="text-sm font-medium text-secondary">Display Density</label>
                <select class="mt-1 w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <option>Comfortable</option>
                    <option>Compact</option>
                    <option>Spacious</option>
                </select>
            </div>

            {{-- Auto-refresh --}}
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-secondary">Auto-refresh Data</label>
                <button type="button" 
                        class="relative inline-flex h-6 w-11 items-center rounded-full bg-secondary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        role="switch"
                        aria-checked="false">
                    <span class="translate-x-1 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                </button>
            </div>

            {{-- Notifications --}}
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-secondary">Module Notifications</label>
                <button type="button" 
                        class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        role="switch"
                        aria-checked="true">
                    <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="border-t border-secondary pt-4">
        <h3 class="text-sm font-semibold text-primary mb-3">Quick Actions</h3>
        <div class="space-y-2">
            <button class="w-full px-3 py-2 text-sm bg-primary text-primary-contrast rounded-md hover:bg-primary/90 transition-colors">
                Export Module Data
            </button>
            <button class="w-full px-3 py-2 text-sm border border-secondary text-primary rounded-md hover:bg-secondary transition-colors">
                Import Module Data
            </button>
            <button class="w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors">
                Reset Module Settings
            </button>
        </div>
    </div>
</div>