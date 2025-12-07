<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    📝 Edit Financial Year
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Update financial year information for {{ $financialYear->name }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="surface shadow-sm rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('accounting.financial-years.update', $financialYear) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="name" class="block text-sm font-medium text-primary">Name</label>
                                <input type="text" id="name" name="name" value="{{ $financialYear->name }}" required
                                       class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm surface">
                            </div>
                            
                            <div>
                                <label for="code" class="block text-sm font-medium text-primary">Code</label>
                                <input type="text" id="code" name="code" value="{{ $financialYear->code }}" required
                                       class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm surface">
                            </div>
                            
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-primary">Start Date</label>
                                <input type="date" id="start_date" name="start_date" value="{{ $financialYear->start_date->format('Y-m-d') }}" required
                                       class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm surface">
                            </div>
                            
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-primary">End Date</label>
                                <input type="date" id="end_date" name="end_date" value="{{ $financialYear->end_date->format('Y-m-d') }}" required
                                       class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm surface">
                            </div>
                        </div>
                        
                        <div>
                            <label for="notes" class="block text-sm font-medium text-primary">Notes</label>
                            <textarea id="notes" name="notes" rows="4"
                                      class="mt-1 block w-full rounded-md border-secondary shadow-sm focus:border-primary focus:ring-primary sm:text-sm surface">{{ $financialYear->notes }}</textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                                Update Financial Year
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>