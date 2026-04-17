<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Maintenance - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.navigation')

        <x-ui.toast />

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5">

            <x-ui.page-header title="Maintenance Team Tasks" subtitle="Overview of all maintenance tasks">
                @can('Create Maintenance Task')
                    <x-slot:actions>
                        <div x-data>
                            <x-ui.button type="button" @click="window.dispatchEvent(new CustomEvent('open-create-task-sheet'))">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                                Add Task
                            </x-ui.button>
                        </div>
                    </x-slot:actions>
                @endcan
            </x-ui.page-header>

            <!-- Pending Tasks -->
            <div class="flex flex-col gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 flex items-center gap-2">
                    <svg class="h-4 w-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    Pending Tasks
                    <x-ui.badge variant="warning">{{ $pendingTasks->count() }}</x-ui.badge>
                </h2>
                <x-maintenance.task-table :tasks="$pendingTasks" emptyMessage="No pending tasks." />
            </div>

            <!-- Completed Tasks -->
            <div class="flex flex-col gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 flex items-center gap-2">
                    <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Completed Tasks
                    <x-ui.badge variant="success">{{ $completedTasks->count() }}</x-ui.badge>
                </h2>
                <x-maintenance.task-table :tasks="$completedTasks" emptyMessage="No completed tasks yet." />
            </div>

        </div>

        <x-maintenance.task-note-sheet />
        @can('Create Maintenance Task')
            <x-maintenance.task-create-sheet />
        @endcan

        @livewireScripts
    </body>
</html>
