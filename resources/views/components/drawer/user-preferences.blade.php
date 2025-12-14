@php
    $user = auth()->user();
    $currentTheme = session('theme', 'light');
    $currentLanguage = session('language', 'en');
    
    // Safely get user roles using the custom role system
    $userRoles = 'Member';
    if ($user && method_exists($user, 'getAllRoles')) {
        $roles = $user->getAllRoles();
        $userRoles = !empty($roles) ? implode(', ', $roles) : 'Member';
    }
@endphp

<div class="p-6 space-y-6">
    {{-- User Profile --}}
    <div class="flex items-center space-x-4 pb-4 border-b border-secondary">
        <div class="w-16 h-16 bg-primary text-primary-contrast rounded-full flex items-center justify-center text-xl font-semibold">
            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-semibold text-primary">{{ $user->name ?? 'User' }}</h3>
            <p class="text-sm text-secondary">{{ $user->email ?? 'user@example.com' }}</p>
            <p class="text-xs text-secondary mt-1">{{ $userRoles }}</p>
        </div>
        <button class="p-2 text-secondary hover:text-primary transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </button>
    </div>

    {{-- Appearance Settings --}}
    <div class="space-y-4">
        <h4 class="text-sm font-semibold text-primary">Appearance</h4>
        
        {{-- Theme Selection --}}
        <div>
            <label class="text-sm font-medium text-secondary mb-2 block">Theme</label>
            <div class="grid grid-cols-3 gap-2">
                <button class="p-3 border-2 border-primary rounded-lg text-center transition-all"
                        :class="$store.theme === 'light' ? 'bg-primary text-primary-contrast' : 'border-secondary hover:border-primary/50'">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="text-xs">Light</span>
                </button>
                <button class="p-3 border-2 border-secondary rounded-lg text-center hover:border-primary/50 transition-all">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <span class="text-xs">Dark</span>
                </button>
                <button class="p-3 border-2 border-secondary rounded-lg text-center hover:border-primary/50 transition-all">
                    <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-xs">System</span>
                </button>
            </div>
        </div>

        {{-- Font Size --}}
        <div>
            <label class="text-sm font-medium text-secondary mb-2 block">Font Size</label>
            <div class="flex items-center space-x-2">
                <button class="px-3 py-1 text-sm border border-secondary rounded hover:bg-secondary transition-colors">A-</button>
                <div class="flex-1 text-center">
                    <span class="text-sm">Medium</span>
                </div>
                <button class="px-3 py-1 text-sm border border-secondary rounded hover:bg-secondary transition-colors">A+</button>
            </div>
        </div>
    </div>

    {{-- Language & Region --}}
    <div class="space-y-4">
        <h4 class="text-sm font-semibold text-primary">Language & Region</h4>
        
        <div>
            <label class="text-sm font-medium text-secondary mb-2 block">Language</label>
            <select class="w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option value="en" {{ $currentLanguage === 'en' ? 'selected' : '' }}>English</option>
                <option value="es" {{ $currentLanguage === 'es' ? 'selected' : '' }}>Español</option>
                <option value="fr" {{ $currentLanguage === 'fr' ? 'selected' : '' }}>Français</option>
                <option value="de" {{ $currentLanguage === 'de' ? 'selected' : '' }}>Deutsch</option>
            </select>
        </div>

        <div>
            <label class="text-sm font-medium text-secondary mb-2 block">Date Format</label>
            <select class="w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>MM/DD/YYYY</option>
                <option>DD/MM/YYYY</option>
                <option>YYYY-MM-DD</option>
            </select>
        </div>

        <div>
            <label class="text-sm font-medium text-secondary mb-2 block">Timezone</label>
            <select class="w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                <option>UTC</option>
                <option>America/New_York</option>
                <option>America/Los_Angeles</option>
                <option>Europe/London</option>
                <option>Asia/Tokyo</option>
            </select>
        </div>
    </div>

    {{-- Notification Preferences --}}
    <div class="space-y-4">
        <h4 class="text-sm font-semibold text-primary">Notifications</h4>
        
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-primary">Email Notifications</div>
                    <div class="text-xs text-secondary">Receive updates via email</div>
                </div>
                <button type="button" 
                        class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                        role="switch"
                        aria-checked="true">
                    <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                </button>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-primary">Browser Notifications</div>
                    <div class="text-xs text-secondary">Desktop notifications</div>
                </div>
                <button type="button" 
                        class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors"
                        role="switch"
                        aria-checked="true">
                    <span class="translate-x-6 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                </button>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-primary">Sound Alerts</div>
                    <div class="text-xs text-secondary">Audio notifications</div>
                </div>
                <button type="button" 
                        class="relative inline-flex h-6 w-11 items-center rounded-full bg-secondary transition-colors"
                        role="switch"
                        aria-checked="false">
                    <span class="translate-x-1 inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Privacy & Security --}}
    <div class="space-y-4">
        <h4 class="text-sm font-semibold text-primary">Privacy & Security</h4>
        
        <div class="space-y-3">
            <button class="w-full text-left px-3 py-2 text-sm border border-secondary rounded-md hover:bg-secondary transition-colors">
                Change Password
            </button>
            <button class="w-full text-left px-3 py-2 text-sm border border-secondary rounded-md hover:bg-secondary transition-colors">
                Two-Factor Authentication
            </button>
            <button class="w-full text-left px-3 py-2 text-sm border border-secondary rounded-md hover:bg-secondary transition-colors">
                Login History
            </button>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex space-x-3 pt-4 border-t border-secondary">
        <button class="flex-1 px-4 py-2 text-sm bg-primary text-primary-contrast rounded-md hover:bg-primary/90 transition-colors">
            Save Preferences
        </button>
        <button class="px-4 py-2 text-sm border border-secondary text-primary rounded-md hover:bg-secondary transition-colors">
            Sign Out
        </button>
    </div>
</div>