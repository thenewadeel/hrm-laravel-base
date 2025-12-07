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
                <svg class="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10 10.586 2.707 2.707a1 1 0 01-1.414 0l-4 4a1 1 0 001.414 0l4-4a1 1 0 010-1.414l-2.293-2.293a1 1 0 00-1.414 1.414L10 10.586l3.293 3.293a1 1 0 001.414-1.414l-4-4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            @endif
        @endforeach
    </ol>
</nav>