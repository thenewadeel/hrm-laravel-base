@extends('membership.layouts.app')

@section('title', 'Edit Member')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Member</h1>
                <p class="text-gray-600 dark:text-gray-400">Update member information</p>
            </div>
            <a href="{{ route('members.index') }}" 
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                Back to Members
            </a>
        </div>
    </div>

    <livewire:membership.member-form :member="$member" />
@endsection