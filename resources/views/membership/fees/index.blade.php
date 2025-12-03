@extends('membership.layouts.app')

@section('title', 'Fees')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Fees</h1>
                <p class="text-gray-600 dark:text-gray-400">Manage member fees and payments</p>
            </div>
            <button wire:click="showCreateFeeForm" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Create Fee
            </button>
        </div>
    </div>

    <livewire:membership.fee-manager />
@endsection