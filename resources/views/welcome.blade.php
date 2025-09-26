<?php
$users = [
    [
        'id' => 1,
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'joined' => '2023-01-15',
    ],
    [
        'id' => 2,
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'joined' => '2022-05-20',
    ],
    [
        'id' => 3,
        'name' => 'Charlie',
        'email' => 'charlie@example.com',
        'joined' => '2024-03-10',
    ],
];
?>

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                {{-- <x-welcome /> --}}
                <x-data-table :headers="[
                    'id' => 'ID',
                    'name' => 'Full Name',
                    'email' => 'Email Address',
                    'joined' => 'Joined Date',
                ]" :data="$users" sort-by="name" sort-direction="asc" />
                <x-button.secondary>OMG</x-button.secondary>
                <x-status-badge />
                <x-loading-spinner />
                <x-empty-state />
                <x-form-section submit="nill">
                    <x-slot name="title">
                        {{ __('Form section head') }}
                    </x-slot>

                    <x-slot name="description">
                        {{ __('Form section desc :Are you sure you would like to remove this person from the team?') }}
                    </x-slot>

                    <x-slot name="form">
                        <x-button.secondary wire:click="$toggle('confirmingTeamMemberRemoval')"
                            wire:loading.attr="disabled">
                            {{ __('Cancel') }}
                        </x-button.secondary>
                    </x-slot>
                </x-form-section>
                <x-dropdown class="">
                    <x-slot name="trigger">
                        <button>
                            qqq
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <a href="#">sss</a>
                        <a href="#"></a>
                    </x-slot> </x-dropdown>
                Confirmation Modal
                <x-confirmation-modal>
                    <x-slot name="title">
                        {{ __('Remove Team Member') }}
                    </x-slot>

                    <x-slot name="content">
                        {{ __('Are you sure you would like to remove this person from the team?') }}
                    </x-slot>

                    <x-slot name="footer">
                        <x-button.secondary wire:click="$toggle('confirmingTeamMemberRemoval')"
                            wire:loading.attr="disabled">
                            {{ __('Cancel') }}
                        </x-button.secondary> Badge
                        <x-badge>

                            <h1>ASDASDASD </h1>
                        </x-badge>
                    </x-slot>
                </x-confirmation-modal>
                Badge
                <x-badge>

                    <h1>ASDASDASD </h1>
                </x-badge>
                Banner?<x-banner />.
                <br />
                Action section
                <x-action-section title="loaded" description="a nice start">
                    <x-slot name="content">
                        <h1>btn </h1>
                        <x-button>
                            <h1>ASDASDASD </h1>
                        </x-button>
                    </x-slot>
                </x-action-section>
                <br />
                App logo
                <x-application-logo />
                <br />
                App Mark
                <x-application-mark />
                <br />

                <br />
                Auth Card
                <x-authentication-card>
                    <x-slot name="logo">
                        <x-authentication-card-logo />
                    </x-slot>

                    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                    </div>


                </x-authentication-card>
            </div>
        </div>
    </div>
</x-app-layout>
