<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            🏢 {{ __('Departments') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-primary sm:text-3xl sm:truncate">
                        Department Management
                    </h2>
                    <p class="mt-1 text-sm text-secondary">
                        Organize your business into departments, branches, and divisions.
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <a href="{{ route('organization.units.create') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Add Department
                    </a>
                </div>
            </div>

            <!-- Filter / Search -->
            <form method="GET" action="{{ route('organization.units.index') }}" class="mb-6">
                <div class="flex flex-wrap gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search departments..."
                        class="block w-full sm:w-64 border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <select name="type"
                        class="block w-full sm:w-48 border-secondary rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All Types</option>
                        <option value="department" {{ request('type') == 'department' ? 'selected' : '' }}>Department</option>
                        <option value="branch" {{ request('type') == 'branch' ? 'selected' : '' }}>Branch</option>
                        <option value="division" {{ request('type') == 'division' ? 'selected' : '' }}>Division</option>
                        <option value="head_office" {{ request('type') == 'head_office' ? 'selected' : '' }}>Head Office</option>
                    </select>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-primary bg-tertiary hover:bg-secondary focus:outline-none">
                        Filter
                    </button>
                    @if(request('search') || request('type'))
                        <a href="{{ route('organization.units.index') }}"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-800">
                            Clear
                        </a>
                    @endif
                </div>
            </form>

            <!-- Departments List -->
            <div class="surface shadow overflow-hidden sm:rounded-md">
                @if($units->count() > 0)
                    <ul class="divide-y divide-secondary">
                        @foreach ($units as $unit)
                            <li>
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-blue-600 font-medium">{{ substr($unit->name, 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-primary">
                                                    {{ $unit->name }}
                                                </div>
                                                <div class="text-sm text-secondary">
                                                    {{ $employeeCounts[$unit->id] ?? 0 }} employees
                                                    @if($unit->parent)
                                                        · under {{ $unit->parent->name }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ ucfirst(str_replace('_', ' ', $unit->type)) }}
                                            </span>
                                            <a href="{{ route('organization.units.edit', $unit) }}"
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-primary hover:bg-secondary">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('organization.units.destroy', $unit) }}"
                                                onsubmit="return confirm('Delete this department? Employees and sub-departments may be affected.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 border border-red-300 rounded-md text-xs font-medium text-red-600 hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No departments found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first department.</p>
                        <a href="{{ route('organization.units.create') }}"
                            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            Add Department
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
