@extends('membership.layouts.app')

@section('title', 'Members')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Members</h1>
                <p class="text-gray-600 dark:text-gray-400">Manage your organization members</p>
            </div>
            <a href="{{ route('members.create') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Add Member
            </a>
        </div>
    </div>

    <livewire:membership.member-list />
@endsection