{{-- Breadcrumb Navigation --}}
@props([
    'pages' => [],
])

<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
        @foreach($pages as $index => $page)
            <li class="flex items-center">
                @if($index === array_key_last($pages))
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $page['title'] }}
                    </span>
                @else
                    @if(isset($page['href']))
                        <a href="{{ $page['href'] }}" 
                           class="text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
                            {{ $page['title'] }}
                        </a>
                    @else
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ $page['title'] }}
                        </span>
                    @endif
                @endif
            </li>
            
            @if($index < array_key_last($pages))
                <x-heroicon-m-chevron-right class="flex-shrink-0 h-5 w-5 text-gray-300" />
            @endif
        @endforeach
    </ol>
</nav>