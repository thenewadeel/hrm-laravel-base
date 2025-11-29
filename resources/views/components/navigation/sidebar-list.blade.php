@props([
    'title' => '',
    'items' => [],
])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
            {{ $title }}
        </h3>
        
        <div class="mt-6 flow-root">
            <ul class="-my-2 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($items as $item)
                    <li class="py-2 flex justify-between space-x-3">
                        <div class="flex-1">
                            <p class="text-sm text-gray-900 dark:text-gray-100 font-medium truncate">
                                {{ $item['label'] }}
                            </p>
                            @if(isset($item['description']))
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item['description'] }}
                                </p>
                            @endif
                        </div>
                        
                        @if(isset($item['value']))
                            <div class="flex-shrink-0 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $item['value'] }}
                            </div>
                        @endif
                        
                        @if(isset($item['href']))
                            <div class="flex-shrink-0">
                                <a href="{{ $item['href'] }}" 
                                   class="inline-flex items-center px-3 py-0.5 border border-transparent text-xs font-medium rounded text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/50 hover:bg-indigo-100 dark:hover:bg-indigo-900 focus:outline-none focus:bg-indigo-100 dark:focus:bg-indigo-900 transition-colors duration-150">
                                    View
                                </a>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>