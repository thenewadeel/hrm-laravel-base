@props([
    'header' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-bg-primary text-primary h-full"
      x-data="{ navOpen: localStorage.getItem('nav-open') === null ? window.innerWidth >= 1024 : localStorage.getItem('nav-open') === 'true' }"
      x-init="$watch('navOpen', v => localStorage.setItem('nav-open', v))"
      @toggle-nav.window="navOpen = !navOpen"
      @keydown.escape.window="navOpen = false">

    <x-banner />

    {{-- Mobile overlay --}}
    <div class="fixed inset-0 z-30 bg-black/50 lg:hidden transition-opacity duration-300"
         x-show="navOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="navOpen = false"
         data-nav-overlay
         aria-hidden="true"></div>

    <div class="flex h-full">
        {{-- Persistent left sidebar --}}
        <aside id="sidebar"
               class="fixed inset-y-0 left-0 z-40 w-72 surface border-r border-secondary flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:z-auto shrink-0"
               :class="navOpen ? 'translate-x-0' : '-translate-x-full'"
               role="navigation"
               aria-label="Navigation">

            {{-- Sidebar brand --}}
            <div class="flex items-center justify-between h-16 px-4 border-b border-secondary shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <x-application-mark class="h-8 w-auto" />
                </a>
                <button @click="navOpen = false"
                        class="p-1.5 rounded-md text-secondary hover:text-primary hover:bg-secondary transition-colors lg:hidden"
                        aria-label="Close navigation">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            {{-- Scrollable nav content --}}
            <div class="flex-1 overflow-y-auto overscroll-contain py-2">
                <x-drawer.app-navigation />
            </div>
        </aside>

        {{-- Main content area --}}
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">

            {{-- Slim app header --}}
            <x-navigation.app-header />

            {{-- Page Heading --}}
            @if ($header)
                <header class="surface border-b border-secondary">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            {{-- Page Content --}}
            <main class="flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-notification-system />
    <x-flash-message duration="9000" />
    @stack('modals')
    <x-navigation.scripts />

    @livewireScripts
</body>

</html>
