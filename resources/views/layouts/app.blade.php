<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1f271f" media="(prefers-color-scheme: dark)">

    {{-- <link rel="preconnect" href="https://fonts.bunny.net"> --}}
    {{-- <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

{{-- FIX: Removed bg-primary to avoid conflicts. Use bg-white or bg-gray-50 as a light mode base. --}}

<body class="font-sans antialiased bg-white text-primary">
    <x-banner />

    {{-- FIX: Removed bg-primary on the main container div as well. --}}
    <div class="min-h-screen bg-stone-100 dark:bg-neutral-800">
        @livewire('navigation-main')
        @if (isset($header))
            <header class="surface shadow-sm border-b border-secondary">
                <div class="Wmax-w-77xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main
            class="surface shadow-inner shadow-lime-200 metallic max-w-7xl flex-1 mx-auto py-2 px-4 sm:px-6 lg:px-8 m-4">
            {{ $slot }}
        </main>
    </div>
    {{-- TODO : Add Footer --}}
    <x-flash-message duration="9000" />
    @stack('modals')
    <x-navigation.scripts />

    @livewireScripts
</body>

</html>
