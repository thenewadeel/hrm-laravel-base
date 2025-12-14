<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    {{-- <link rel="preconnect" href="https://fonts.bunny.net"> --}}
    {{-- <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> --}}

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body x-data="{
    test: 'working',
    drawers: {
        left: false,
        right: false,
        top: false,
        bottom: false
    },
    drawerState(position) {
        return this.drawers[position] || false;
    },
    toggleDrawer(position) {
        const newState = !this.drawers[position];
        this.closeAllDrawers();
        this.drawers[position] = newState;

        // Log for debugging
        console.log(`Drawer ${position} is now: ${newState ? 'OPEN' : 'CLOSED'}`);

        // Focus management
        if (newState) {
            this.$nextTick(() => {
                const drawerEl = document.querySelector(`.drawer-${position}`);
                if (drawerEl) {
                    drawerEl.focus();
                }
            });
        }
    },
    closeDrawer(position) {
        this.drawers[position] = false;
    },
    closeAllDrawers() {
        Object.keys(this.drawers).forEach(key => {
            this.drawers[key] = false;
        });
    },
    handleKeydown(e) {
        if (e.key === 'Escape') {
            this.closeAllDrawers();
        }
    },
    init() {
        console.log('Drawer system initialized:', this.test);
        console.log('Initial drawer state:', this.drawers);

        // Prevent layout shift warning
        this.$nextTick(() => {
            console.log('Layout fully loaded');
        });
    }
}" x-init="init()" @keydown.window="handleKeydown($event)"
    class="font-sans antialiased bg-primary text-primary">

    {{-- Debug indicator (remove in production) --}}
    {{-- <div class="fixed top-4 right-4 bg-red-500 text-white p-2 rounded z-50 text-sm">
        Test: <span x-text="test"></span>
    </div> --}}

    <x-banner />
    @livewire('navigation-main')

    <div class="drawer-container relative min-h-screen" role="application">
        {{-- Header with drawer toggles --}}
        <header class="surface shadow-sm border-b border-secondary sticky top-0 z-30">
            <div class="px-4 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    {{-- Left drawer toggle (navigation) --}}
                    <x-drawer.toggle target="drawer-left" label="Toggle navigation menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </x-drawer.toggle>

                    {{-- Page title or branding --}}
                    @hasSection('header')
                        @yield('header')
                    @else
                        @if (isset($header))
                            {{ $header }}
                        @else
                            <h1 class="text-xl font-semibold">{{ config('app.name') }}</h1>
                        @endif
                    @endif
                </div>

                <div class="flex items-center space-x-2">
                    {{-- Top drawer toggle (app info) --}}
                    <x-drawer.toggle target="drawer-top" label="Toggle app information" class="hidden sm:flex">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </x-drawer.toggle>

                    {{-- Right drawer toggle (module settings) --}}
                    <x-drawer.toggle target="drawer-right" label="Toggle module settings">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </x-drawer.toggle>

                    {{-- Bottom drawer toggle (user preferences) --}}
                    <x-drawer.toggle target="drawer-bottom" label="Toggle user preferences">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </x-drawer.toggle>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1">
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot }}
            @endif
        </main>

        {{-- Left Drawer - App Navigation --}}
        <x-drawer position="left" title="Navigation" width="320px">
            <x-drawer.app-navigation />
        </x-drawer>

        {{-- Right Drawer - Module Settings --}}
        <x-drawer position="right" title="Module Settings" width="360px">
            <x-drawer.module-settings />
        </x-drawer>

        {{-- Top Drawer - App Information --}}
        <x-drawer position="top" title="App Information" height="320px">
            <x-drawer.app-info />
        </x-drawer>

        {{-- Bottom Drawer - User Preferences --}}
        <x-drawer position="bottom" title="User Preferences" height="400px">
            <x-drawer.user-preferences />
        </x-drawer>

        {{-- Drawer Overlay --}}
        <x-drawer.overlay />
    </div>

    <x-notification-system />
    <x-flash-message duration="9000" />
    @stack('modals')
    <x-navigation.scripts />

    @livewireScripts
</body>

</html>
