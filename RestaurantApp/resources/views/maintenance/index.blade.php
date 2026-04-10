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
                    Onderhoud
                </p>
                <h2 class="text-2xl font-semibold text-slate-900">
                    Onderhoudsploeg Taken
                </h2>
                <p class="text-sm text-slate-500">
                    Overzicht van alle taken van de receptie
                </p>
            </div>

            @php
                $pendingTasks = collect($tasks)->where('status', 'pending');
                $completedTasks = collect($tasks)->where('status', 'completed');
            @endphp

            <!-- Statistics Summary -->
            <div class="grid gap-4 sm:grid-cols-3 mb-8">
                <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-6 shadow-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ count($tasks) }}</p>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Totaal Taken</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-6 shadow-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100">
                            <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ collect($tasks)->where('status', 'pending')->count() }}</p>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">In Behandeling</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-6 shadow-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ collect($tasks)->where('status', 'completed')->count() }}</p>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Gereed</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Table -->
            <div class="rounded-2xl border border-slate-200/70 bg-white/80 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b-2 border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-700">Taak</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-700">Datum</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-700">Notities</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" x-data="{ expandedTask: null }">
                            @foreach($tasks as $index => $task)
                                <!-- Main Row -->
                                <tr class="cursor-pointer transition-all hover:bg-slate-50/50 @if($task['status'] === 'completed') border-l-4 border-green-600 @else border-l-4 border-amber-600 @endif"
                                    x-data="{ task: @js($task) }"
                                    @click="expandedTask = expandedTask === {{ $index }} ? null : {{ $index }}"
                                    :class="{ 
                                        'bg-slate-50': expandedTask === {{ $index }}
                                    }">
                                    <td class="px-6 py-3.5">
                                        <p class="text-sm font-medium text-slate-900">{{ $task['name'] }}</p>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <p class="text-sm text-slate-600">{{ \Carbon\Carbon::parse($task['date'])->format('d-m-Y H:i') }}</p>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <p class="text-sm text-slate-500 truncate max-w-xs" x-text="task.notes || '-'"></p>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <!-- Only Icon -->
                                        <svg x-show="task.status === 'completed'" class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <svg x-show="task.status === 'pending'" class="h-5 w-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                    </td>
                                </tr>
                                
                                <!-- Expanded Details Row -->
                                <tr x-show="expandedTask === {{ $index }}" 
                                    x-data="{ task: @js($task) }"
                                    style="display: none;"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    class="bg-slate-50 border-b border-slate-100">
                                    <td colspan="4" class="px-6 py-6" @click.stop>
                                        <div class="space-y-4">
                                            <!-- Notes Section -->
                                            <div>
                                                <label class="block text-sm font-semibold text-slate-700 mb-2">Opmerkingen</label>
                                                <textarea 
                                                    x-model="task.notes"
                                                    rows="4" 
                                                    placeholder="Voeg opmerkingen toe..."
                                                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-blue-400 focus:ring-2 focus:ring-blue-200 resize-none"
                                                ></textarea>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="flex items-center justify-end gap-3">
                                                <button 
                                                    @click="expandedTask = null"
                                                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200 transition">
                                                    Sluiten
                                                </button>
                                                <button 
                                                    @click="task.status = task.status === 'completed' ? 'pending' : 'completed'"
                                                    class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition"
                                                    :class="task.status === 'completed' 
                                                        ? 'bg-amber-500 hover:bg-amber-600' 
                                                        : 'bg-green-500 hover:bg-green-600'">
                                                    <span x-text="task.status === 'completed' ? 'Markeer als in behandeling' : 'Markeer als gereed'"></span>
                                                </button>
                                            </div>
                                        </div>
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
