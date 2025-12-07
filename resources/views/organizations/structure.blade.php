<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🏢 {{ $organization->name }} Structure
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Organizational hierarchy and department structure
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Organization Tree</h3>
                    
                    @if($tree->count() > 0)
                        <div class="space-y-4">
                            @foreach($tree as $unit)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                                    <span class="text-white font-medium">{{ substr($unit->name, 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-medium text-gray-900">{{ $unit->name }}</h4>
                                                <p class="text-sm text-gray-500">{{ $unit->users->count() }} employees</p>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $unit->type ?? 'Department' }}
                                        </div>
                                    </div>
                                    
                                    @if($unit->children->count() > 0)
                                        <div class="mt-4 ml-8 space-y-2">
                                            @foreach($unit->children as $child)
                                                <div class="border-l-2 border-gray-200 pl-4 py-2">
                                                    <div class="flex items-center space-x-2">
                                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-gray-700 text-sm font-medium">{{ substr($child->name, 0, 1) }}</span>
                                                        </div>
                                                        <div>
                                                            <h5 class="text-sm font-medium text-gray-900">{{ $child->name }}</h5>
                                                            <p class="text-xs text-gray-500">{{ $child->users->count() }} employees</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No departments</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first department.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>