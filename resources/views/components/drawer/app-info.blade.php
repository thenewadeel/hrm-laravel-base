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
                <x-heroicon-o-book-open class="w-5 h-5 mr-3 text-primary" />
                <div>
                    <div class="text-sm font-medium">User Guide</div>
                    <div class="text-xs text-secondary">Complete documentation</div>
                </div>
            </a>

            <a href="#" class="flex items-center p-3 bg-secondary/10 rounded-lg hover:bg-secondary/20 transition-colors">
                <x-heroicon-o-question-mark-circle class="w-5 h-5 mr-3 text-primary" />
                <div>
                    <div class="text-sm font-medium">FAQ</div>
                    <div class="text-xs text-secondary">Frequently asked questions</div>
                </div>
            </a>

            <a href="#" class="flex items-center p-3 bg-secondary/10 rounded-lg hover:bg-secondary/20 transition-colors">
                <x-heroicon-o-play-circle class="w-5 h-5 mr-3 text-primary" />
                <div>
                    <div class="text-sm font-medium">Video Tutorials</div>
                    <div class="text-xs text-secondary">Step-by-step guides</div>
                </div>
            </a>

            <a href="#" class="flex items-center p-3 bg-secondary/10 rounded-lg hover:bg-secondary/20 transition-colors">
                <x-heroicon-o-information-circle class="w-5 h-5 mr-3 text-primary" />
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