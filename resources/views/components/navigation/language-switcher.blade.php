{{-- Language Switcher --}}
@props([
    'currentLocale' => 'en',
    'availableLocales' => [],
])

<div class="relative">
    <button
        class="p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <span class="sr-only">Change language</span>

        <!-- Globe Icon -->
        <x-heroicon-o-globe-alt class="h-5 w-5" />
    </button>

    <!-- Language Dropdown -->
    <div
        class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50">
        <div class="py-1" role="menu">
            @foreach ($availableLocales as $locale => $name)
                <a {{-- TODO: href="{{ route('language.switch', $locale) }}"  --}}
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:text-gray-900 dark:hover:text-gray-100 transition-colors duration-150 ease-in-out {{ $currentLocale === $locale ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                    {{ $name }}
                </a>
            @endforeach
        </div>
    </div>
</div>
