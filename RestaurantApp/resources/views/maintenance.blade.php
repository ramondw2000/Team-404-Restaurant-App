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
        <x-ui.confirm-modal />

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

            {{-- Search & Filters --}}
            <form method="GET" action="{{ route('maintenance') }}" class="flex flex-col gap-3">
                {{-- Search bar --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks, notes, or assigned user…" class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-molveno-blue-300 focus:border-molveno-blue-400 placeholder:text-gray-400" />
                    </div>
                    <x-ui.button type="submit" variant="secondary" size="sm">Search</x-ui.button>
                    @if(request()->hasAny(['search', 'filter', 'status']))
                        <a href="{{ route('maintenance') }}" class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-gray-700 px-2 py-1">
                            Clear filters
                        </a>
                    @endif
                </div>

                {{-- Quick filters + Status filters --}}
                <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                    {{-- Quick filters (radio-style) --}}
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-medium text-gray-500 mr-1">Show:</span>
                        @php
                            $currentFilter = request('filter', 'all');
                        @endphp
                        <a href="{{ route('maintenance', array_merge(request()->except('filter', 'page'), $currentFilter !== 'all' ? [] : [])) }}"
                            class="px-2.5 py-1 text-xs font-medium rounded-full transition-colors {{ $currentFilter === 'all' || !$currentFilter ? 'bg-molveno-blue-100 text-molveno-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            All <span class="text-[0.65rem] opacity-70">({{ $statusCounts['all'] }})</span>
                        </a>
                        <a href="{{ route('maintenance', array_merge(request()->except('page'), ['filter' => 'my-tasks'])) }}"
                            class="px-2.5 py-1 text-xs font-medium rounded-full transition-colors {{ $currentFilter === 'my-tasks' ? 'bg-molveno-blue-100 text-molveno-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            My Tasks
                        </a>
                        <a href="{{ route('maintenance', array_merge(request()->except('page'), ['filter' => 'unassigned'])) }}"
                            class="px-2.5 py-1 text-xs font-medium rounded-full transition-colors {{ $currentFilter === 'unassigned' ? 'bg-molveno-blue-100 text-molveno-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Unassigned
                        </a>
                    </div>

                    <div class="hidden sm:block w-px h-5 bg-gray-200"></div>

                    {{-- Status filter checkboxes --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-500 mr-1">Status:</span>
                        @php
                            $selectedStatuses = (array) request('status', []);
                        @endphp
                        @foreach(\App\Enums\MaintenanceTaskStatus::cases() as $statusCase)
                            <label class="inline-flex items-center gap-1 text-xs cursor-pointer">
                                <input type="checkbox" name="status[]" value="{{ $statusCase->value }}"
                                    {{ in_array($statusCase->value, $selectedStatuses) ? 'checked' : '' }}
                                    onchange="this.form.submit()"
                                    class="rounded border-gray-300 text-molveno-blue-500 focus:ring-molveno-blue-300 w-3.5 h-3.5"
                                />
                                <x-ui.badge :variant="$statusCase->badgeVariant()" size="sm">
                                    {{ $statusCase->label() }}
                                    <span class="opacity-70">({{ $statusCounts[$statusCase->value] ?? 0 }})</span>
                                </x-ui.badge>
                            </label>
                        @endforeach
                    </div>
                </div>
            </form>

            {{-- Task Table --}}
            <x-maintenance.task-table :tasks="$tasks" emptyMessage="No tasks found matching your filters." />

            {{-- Pagination --}}
            @if($tasks->hasPages())
                <div class="flex justify-center">
                    {{ $tasks->links() }}
                </div>
            @endif

        </div>

        <x-maintenance.task-note-sheet />
        @can('Create Maintenance Task')
            <x-maintenance.task-create-sheet />
        @endcan

        @livewireScripts
    </body>
</html>
