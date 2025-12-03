@extends('membership.layouts.app')

@section('title', 'Card Printing')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Card Printing</h1>
                <p class="text-gray-600 dark:text-gray-400">Design and print membership cards</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('cards.batch') }}" 
                   class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    Batch Printing
                </a>
            </div>
        </div>
    </div>

    <livewire:membership.card-designer />
@endsection