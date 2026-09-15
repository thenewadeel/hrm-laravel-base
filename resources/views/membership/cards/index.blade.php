<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-primary leading-tight">
                    🪪 Card Printing
                </h2>
                <p class="text-sm text-secondary mt-1">
                    Design and print membership cards
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('cards.batch') }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <x-heroicon-o-credit-card class="w-4 h-4 mr-2" />
                    Batch Printing
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:membership.card-designer />
        </div>
    </div>
</x-app-layout>