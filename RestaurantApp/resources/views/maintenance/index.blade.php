<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Onderhoud - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#E8EEF3]">
        @include('layouts.navigation')

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5">

            <!-- Page Header -->
            <div class="flex flex-col gap-1">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                    Maintenance
                </p>
                <h2 class="text-2xl font-semibold text-slate-900">
                    Maintenance Team Tasks
                </h2>
                <p class="text-sm text-slate-500">
                    Overview of all maintenance tasks
                </p>
            </div>

            <!-- Pending Tasks Section -->
            <div class="bg-white rounded-lg border border-amber-200 overflow-hidden">
                <div class="bg-amber-50 px-4 py-2 border-b border-amber-200">
                    <h3 class="font-semibold text-amber-900 flex items-center gap-2">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Pending Tasks {{ $pendingTasks->count() }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Task</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Notes</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($pendingTasks as $task)
                                <tr class="hover:bg-amber-50/50">
                                    <td class="px-3 py-2">
                                        <span class="text-sm font-medium text-slate-900">{{ $task->name }}</span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <span class="text-sm text-slate-600">{{ $task->created_at->format('M d, Y') }}</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-sm text-slate-600">{{ $task->notes ?? '—' }}</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 text-xs font-medium rounded text-amber-700 bg-amber-50">Pending</span>
                                            <form method="POST" action="{{ route('maintenance.markAsDone', $task) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="px-2 py-1 text-xs font-medium text-white bg-green-500 hover:bg-green-600 rounded transition">
                                                    Mark as Done
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Completed Tasks Section -->
            <div class="bg-white rounded-lg border border-green-200 overflow-hidden">
                <div class="bg-green-50 px-4 py-2 border-b border-green-200">
                    <h3 class="font-semibold text-green-900 flex items-center gap-2">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Completed Tasks {{ $completedTasks->count() }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Task</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Notes</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-slate-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($completedTasks as $task)
                                <tr class="hover:bg-green-50/50">
                                    <td class="px-3 py-2">
                                        <span class="text-sm font-medium text-slate-900">{{ $task->name }}</span>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <span class="text-sm text-slate-600">{{ $task->updated_at->format('M d, Y') }}</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="text-sm text-slate-600">{{ $task->notes ?? '—' }}</span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-1 text-xs font-medium rounded text-green-700 bg-green-50">Done</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </body>
</html>
