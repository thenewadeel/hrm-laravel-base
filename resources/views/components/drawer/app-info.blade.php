@php
    $appVersion = config('app.version', '1.0.0');
    $laravelVersion = app()->version();
    $phpVersion = PHP_VERSION;
    $dbConnection = config('database.default');
    $memoryUsage = round(memory_get_usage() / 1024 / 1024, 2);
    $peakMemory = round(memory_get_peak_usage() / 1024 / 1024, 2);
@endphp

<div class="p-6 space-y-6">
    {{-- System Status --}}
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center space-x-2">
            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
            <h3 class="text-sm font-semibold text-green-800">System Status: Online</h3>
        </div>
        <p class="text-xs text-green-600 mt-1">All systems operational</p>
    </div>

    {{-- Application Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-primary">Application</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-secondary">Name:</span>
                    <span class="font-medium">{{ config('app.name') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-secondary">Version:</span>
                    <span class="font-medium">{{ $appVersion }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-secondary">Environment:</span>
                    <span class="font-medium">{{ config('app.env') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-secondary">Debug Mode:</span>
                    <span class="font-medium">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</span>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-sm font-semibold text-primary">Technical Details</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-secondary">Laravel:</span>
                    <span class="font-medium">{{ $laravelVersion }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-secondary">PHP:</span>
                    <span class="font-medium">{{ $phpVersion }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-secondary">Database:</span>
                    <span class="font-medium">{{ ucfirst($dbConnection) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-secondary">Memory Usage:</span>
                    <span class="font-medium">{{ $memoryUsage }} MB</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Help --}}
    <div class="space-y-3">
        <h4 class="text-sm font-semibold text-primary">Quick Help</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <a href="#" class="flex items-center p-3 bg-secondary/10 rounded-lg hover:bg-secondary/20 transition-colors">
                <svg class="w-5 h-5 mr-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <div>
                    <div class="text-sm font-medium">User Guide</div>
                    <div class="text-xs text-secondary">Complete documentation</div>
                </div>
            </a>

            <a href="#" class="flex items-center p-3 bg-secondary/10 rounded-lg hover:bg-secondary/20 transition-colors">
                <svg class="w-5 h-5 mr-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <div class="text-sm font-medium">FAQ</div>
                    <div class="text-xs text-secondary">Frequently asked questions</div>
                </div>
            </a>

            <a href="#" class="flex items-center p-3 bg-secondary/10 rounded-lg hover:bg-secondary/20 transition-colors">
                <svg class="w-5 h-5 mr-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <div>
                    <div class="text-sm font-medium">Video Tutorials</div>
                    <div class="text-xs text-secondary">Step-by-step guides</div>
                </div>
            </a>

            <a href="#" class="flex items-center p-3 bg-secondary/10 rounded-lg hover:bg-secondary/20 transition-colors">
                <svg class="w-5 h-5 mr-3 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <div class="text-sm font-medium">Release Notes</div>
                    <div class="text-xs text-secondary">Latest updates</div>
                </div>
            </a>
        </div>
    </div>

    {{-- Keyboard Shortcuts --}}
    <div class="space-y-3">
        <h4 class="text-sm font-semibold text-primary">Keyboard Shortcuts</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
            <div class="flex justify-between p-2 bg-secondary/5 rounded">
                <span class="text-secondary">Toggle Navigation</span>
                <kbd class="px-2 py-1 text-xs bg-secondary rounded">Ctrl+L</kbd>
            </div>
            <div class="flex justify-between p-2 bg-secondary/5 rounded">
                <span class="text-secondary">Toggle Settings</span>
                <kbd class="px-2 py-1 text-xs bg-secondary rounded">Ctrl+R</kbd>
            </div>
            <div class="flex justify-between p-2 bg-secondary/5 rounded">
                <span class="text-secondary">Toggle Info</span>
                <kbd class="px-2 py-1 text-xs bg-secondary rounded">Ctrl+T</kbd>
            </div>
            <div class="flex justify-between p-2 bg-secondary/5 rounded">
                <span class="text-secondary">Toggle Preferences</span>
                <kbd class="px-2 py-1 text-xs bg-secondary rounded">Ctrl+B</kbd>
            </div>
            <div class="flex justify-between p-2 bg-secondary/5 rounded">
                <span class="text-secondary">Close All Drawers</span>
                <kbd class="px-2 py-1 text-xs bg-secondary rounded">Esc</kbd>
            </div>
        </div>
    </div>

    {{-- Support Information --}}
    <div class="border-t border-secondary pt-4">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-semibold text-primary">Need Help?</h4>
                <p class="text-xs text-secondary mt-1">Contact our support team</p>
            </div>
            <button class="px-4 py-2 text-sm bg-primary text-primary-contrast rounded-md hover:bg-primary/90 transition-colors">
                Contact Support
            </button>
        </div>
    </div>
</div>