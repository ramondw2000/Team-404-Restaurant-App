<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Sales Statistics - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.navigation')

        <x-ui.toast />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex flex-col gap-6">

            {{-- Page header --}}
            <div class="bg-gradient-to-br from-primary to-molveno-blue-700 rounded-2xl px-8 py-8 text-white shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-white/60 text-xs font-semibold uppercase tracking-widest mb-1">Molveno Analytics</p>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold">Sales performance overview</h1>
                            <button
                                type="button"
                                x-data
                                x-on:click="$dispatch('open-sheet', { name: 'help-statistics' })"
                                class="p-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-colors"
                                title="How this page works"
                                aria-label="Open statistics help"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01"/>
                                    <circle cx="12" cy="20" r="1" fill="currentColor"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-white/70 mt-1 text-sm">Monitor channel performance, track top dishes, and review completed orders.</p>
                    </div>
                    <x-help.sheet page="statistics" title="How to read the Statistics page" />
                    <div class="shrink-0 flex items-center gap-3">
                        {{-- Period selector buttons --}}
                        <div class="bg-white/10 border border-white/20 rounded-xl p-1 flex items-center gap-1">
                            @php
                                $periodTitles = [
                                    'day' => 'Show data for today only',
                                    'week' => 'Show data for the last 7 days',
                                    'month' => 'Show data for the current month',
                                    'year' => 'Show data for the current year',
                                ];
                            @endphp
                            @foreach(['day' => 'Today', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'] as $key => $label)
                                <a href="{{ route('statistics', ['period' => $key]) }}"
                                   title="{{ $periodTitles[$key] }}"
                                   class="relative px-4 py-1.5 text-sm font-medium rounded-lg transition-all duration-200 ease-out {{ $period === $key ? 'bg-white text-primary shadow-lg shadow-black/20 font-bold' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                        <div class="bg-white/10 border border-white/20 rounded-xl px-4 py-2.5 text-sm font-medium text-white/90 flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3v18h18"/><path d="m7 14 4-4 4 4 4-8"/>
                            </svg>
                            Live &middot; {{ now()->format('M j, H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI summary cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-primary rounded-xl p-6 shadow-sm">
                    <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center text-white mb-4">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-white/60 uppercase tracking-widest">Total Sales</p>
                    <p class="text-3xl font-bold text-white mt-1">€ {{ number_format($totalSales, 2) }}</p>
                    <p class="text-sm text-white/60 mt-1">{{ $orderCount }} completed orders</p>
                </div>
                <div class="bg-molveno-blue-700 rounded-xl p-6 shadow-sm">
                    <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center text-white mb-4">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3v18h18"/><path d="m7 14 4-4 4 4 4-8"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-white/60 uppercase tracking-widest">Average Order Value</p>
                    <p class="text-3xl font-bold text-white mt-1">€ {{ number_format($averageOrderValue, 2) }}</p>
                    <p class="text-sm text-white/60 mt-1">Per order ({{ ucfirst($period) }})</p>
                </div>
                <div class="bg-molveno-blue-500 rounded-xl p-6 shadow-sm">
                    <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center text-white mb-4">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-white/60 uppercase tracking-widest">Completed Orders</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $orderCount }}</p>
                    <p class="text-sm text-white/60 mt-1">Ready for final review</p>
                </div>
            </div>

            {{-- Sales breakdown & latest orders --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- Left: channel breakdown + top dishes --}}
                <div class="lg:col-span-2 flex flex-col gap-5">

                    {{-- Channel performance --}}
                    <x-ui.card padding="none" class="bg-slate-100 border-0">
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-3 mb-5">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Channel Performance</p>
                                <h3 class="text-base font-semibold text-gray-900 mt-0.5">Sales breakdown</h3>
                            </div>
                            <x-ui.badge variant="primary" class="bg-primary text-white">
                                € {{ number_format($totalSales, 2) }} total
                            </x-ui.badge>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach($salesByType as $channel)
                                <div class="bg-white rounded-xl p-5 flex flex-col gap-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">{{ $channel['label'] }}</p>
                                            <p class="text-xl font-bold text-gray-900 mt-1">€ {{ number_format($channel['sales'], 2) }}</p>
                                        </div>
                                        <x-ui.badge variant="primary" class="shrink-0 bg-molveno-blue-700 text-white">
                                            {{ $channel['orders'] }} orders
                                        </x-ui.badge>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                        <span class="block h-full rounded-full {{ $channel['key'] === 'restaurant' ? 'bg-molveno-blue-500' : 'bg-molveno-blue-300' }}"
                                              style="width: {{ min(100, $channel['share']) }}%"></span>
                                    </div>
                                    <p class="text-xs text-gray-400">{{ $channel['share'] }}% of daily revenue</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    </x-ui.card>

                    {{-- Dish statistics --}}
                    <x-ui.card padding="none" class="bg-slate-100 border-0">
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-3 mb-5">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Dish Performance</p>
                                <h3 class="text-base font-semibold text-gray-900 mt-0.5">Kitchen dishes</h3>
                            </div>
                            <x-ui.badge variant="primary" class="bg-primary text-white">
                                € {{ number_format($totalDishRevenue, 2) }} revenue
                            </x-ui.badge>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-5">
                            {{-- Most sold dishes --}}
                            <div>
                                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                                    Most Sold
                                </p>
                                <div class="flex flex-col gap-2">
                                    @forelse($topItems as $index => $item)
                                        <div class="flex items-center justify-between bg-white rounded-xl px-4 py-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="shrink-0 w-7 h-7 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center">
                                                    {{ $index + 1 }}
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $item['name'] }}</p>
                                                    <p class="text-xs text-gray-400">{{ $item['qty'] }} sold</p>
                                                </div>
                                            </div>
                                            <x-ui.badge variant="primary" class="shrink-0 bg-primary text-white">
                                                € {{ number_format($item['revenue'], 2) }}
                                            </x-ui.badge>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-400">No dish sales recorded yet.</p>
                                    @endforelse
                                </div>
                            </div>
                            {{-- Least sold dishes --}}
                            <div>
                                <p class="text-xs font-semibold text-amber-600 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                    Least Sold
                                </p>
                                <div class="flex flex-col gap-2">
                                    @forelse($leastSoldDishes as $index => $item)
                                        <div class="flex items-center justify-between bg-white rounded-xl px-4 py-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="shrink-0 w-7 h-7 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center">
                                                    {{ $index + 1 }}
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $item['name'] }}</p>
                                                    <p class="text-xs text-gray-400">{{ $item['qty'] }} sold</p>
                                                </div>
                                            </div>
                                            <x-ui.badge variant="primary" class="shrink-0 bg-primary text-white">
                                                € {{ number_format($item['revenue'], 2) }}
                                            </x-ui.badge>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-400">No dish sales recorded yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    </x-ui.card>
                </div>

                {{-- Right: latest orders --}}
                <x-ui.card padding="none" class="bg-slate-100 border-0">
                <div class="p-6 flex flex-col gap-5">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Recent Highlights</p>
                        <h3 class="text-base font-semibold text-gray-900 mt-0.5">Latest orders</h3>
                    </div>
                    <div class="flex flex-col gap-3">
                        @foreach($recentOrders as $order)
                            <div class="bg-white border-l-4 border-molveno-blue-500 rounded-xl p-4 flex flex-col gap-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900">{{ $order['id'] }}</span>
                                    <span class="text-xs text-gray-400">{{ $order['closed_at'] }}</span>
                                </div>
                                <p class="text-xs text-gray-400">{{ $order['location'] }} &middot; {{ $order['waiter'] }} &middot; {{ $order['customer'] }}</p>
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-xs text-gray-400">{{ count($order['items']) }} items</span>
                                    <x-ui.badge variant="primary" class="bg-molveno-blue-700 text-white">€ {{ number_format($order['total'], 2) }}</x-ui.badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                </x-ui.card>
            </div>

            {{-- Bar drinks statistics --}}
            <x-ui.card padding="none" class="bg-slate-100 border-0">
            <div class="p-6">
                <div class="flex items-center justify-between gap-3 mb-5">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Bar Performance</p>
                        <h3 class="text-base font-semibold text-gray-900 mt-0.5">Bar drinks</h3>
                    </div>
                    <x-ui.badge variant="primary" class="bg-molveno-blue-700 text-white">
                        € {{ number_format($totalBarRevenue, 2) }} revenue
                    </x-ui.badge>
                </div>
                <div class="grid sm:grid-cols-2 gap-5">
                    {{-- Most sold bar drinks --}}
                    <div>
                        <p class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                            Most Sold
                        </p>
                        <div class="flex flex-col gap-2">
                            @forelse($topBarDrinks as $index => $item)
                                <div class="flex items-center justify-between bg-white rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="shrink-0 w-7 h-7 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center">
                                            {{ $index + 1 }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $item['name'] }}</p>
                                            <p class="text-xs text-gray-400">{{ $item['qty'] }} sold</p>
                                        </div>
                                    </div>
                                    <x-ui.badge variant="primary" class="shrink-0 bg-molveno-blue-700 text-white">
                                        € {{ number_format($item['revenue'], 2) }}
                                    </x-ui.badge>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400">No bar drink sales recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                    {{-- Least sold bar drinks --}}
                    <div>
                        <p class="text-xs font-semibold text-amber-600 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            Least Sold
                        </p>
                        <div class="flex flex-col gap-2">
                            @forelse($leastSoldBarDrinks as $index => $item)
                                <div class="flex items-center justify-between bg-white rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="shrink-0 w-7 h-7 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center">
                                            {{ $index + 1 }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $item['name'] }}</p>
                                            <p class="text-xs text-gray-400">{{ $item['qty'] }} sold</p>
                                        </div>
                                    </div>
                                    <x-ui.badge variant="primary" class="shrink-0 bg-molveno-blue-700 text-white">
                                        € {{ number_format($item['revenue'], 2) }}
                                    </x-ui.badge>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400">No bar drink sales recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            </x-ui.card>

            {{-- Unsold items (compact) --}}
            @if($unsoldDishes->isNotEmpty() || $unsoldBarDrinks->isNotEmpty())
            <x-ui.card padding="none" class="bg-slate-100 border-0">
            <div class="p-6">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Zero Sales</p>
                        <h3 class="text-base font-semibold text-gray-900 mt-0.5">Not sold this {{ $period === 'day' ? 'day' : $period }}</h3>
                    </div>
                    <x-ui.badge variant="primary" class="bg-red-500 text-white">
                        {{ $unsoldDishes->count() + $unsoldBarDrinks->count() }} items
                    </x-ui.badge>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    {{-- Unsold dishes --}}
                    <div x-data="{ limit: 8 }">
                        <p class="text-xs font-semibold text-red-500 uppercase tracking-widest mb-2">Unsold Dishes ({{ $unsoldDishes->count() }})</p>
                        @if($unsoldDishes->isEmpty())
                            <p class="text-xs text-gray-400">All dishes have been sold!</p>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($unsoldDishes as $index => $name)
                                    <span x-show="{{ $index }} < limit" class="inline-flex items-center rounded-lg bg-white px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-200">{{ $name }}</span>
                                @endforeach
                            </div>
                            @if($unsoldDishes->count() > 8)
                                <button x-show="limit === 8" x-on:click="limit = {{ $unsoldDishes->count() }}" class="mt-2 text-xs font-medium text-primary hover:underline">
                                    Show all {{ $unsoldDishes->count() }} &rarr;
                                </button>
                                <button x-show="limit !== 8" x-on:click="limit = 8" x-cloak class="mt-2 text-xs font-medium text-primary hover:underline">
                                    Show less &larr;
                                </button>
                            @endif
                        @endif
                    </div>
                    {{-- Unsold bar drinks --}}
                    <div x-data="{ limit: 8 }">
                        <p class="text-xs font-semibold text-red-500 uppercase tracking-widest mb-2">Unsold Bar Drinks ({{ $unsoldBarDrinks->count() }})</p>
                        @if($unsoldBarDrinks->isEmpty())
                            <p class="text-xs text-gray-400">All bar drinks have been sold!</p>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($unsoldBarDrinks as $index => $name)
                                    <span x-show="{{ $index }} < limit" class="inline-flex items-center rounded-lg bg-white px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-200">{{ $name }}</span>
                                @endforeach
                            </div>
                            @if($unsoldBarDrinks->count() > 8)
                                <button x-show="limit === 8" x-on:click="limit = {{ $unsoldBarDrinks->count() }}" class="mt-2 text-xs font-medium text-primary hover:underline">
                                    Show all {{ $unsoldBarDrinks->count() }} &rarr;
                                </button>
                                <button x-show="limit !== 8" x-on:click="limit = 8" x-cloak class="mt-2 text-xs font-medium text-primary hover:underline">
                                    Show less &larr;
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            </x-ui.card>
            @endif

            {{-- Completed orders table (Livewire component) --}}
            <livewire:completed-order-table />

        </div>
    @livewireScripts
</body>
</html>