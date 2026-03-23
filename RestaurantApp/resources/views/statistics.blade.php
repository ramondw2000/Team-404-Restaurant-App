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

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 flex flex-col gap-6">

            <div class="bg-gradient-to-br from-primary to-molveno-blue-700 rounded-3xl px-6 sm:px-10 py-8 text-white shadow-xl">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                    <div>
                        <p class="text-white/70 text-xs font-semibold uppercase tracking-[0.2em]">Molveno analytics</p>
                        <h1 class="text-3xl font-black mt-2">Sales performance overview</h1>
                        <p class="text-white/80 mt-2 max-w-2xl text-sm">Monitor floor & room-service performance, track top-selling dishes, and stay ahead of trends in a single glance.</p>
                    </div>
                    <div class="bg-white/15 rounded-2xl px-5 py-3 text-sm font-semibold text-white flex items-center gap-3">
                        <span class="inline-flex w-9 h-9 rounded-full bg-white/20 items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3v18h18"/>
                                <path d="m7 14 4-4 4 4 4-8"/>
                            </svg>
                        </span>
                        Live snapshot &middot; {{ now()->format('M j, H:i') }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-lg shadow-sky-100 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-200">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em]">Total Sales</p>
                    <h2 class="text-3xl font-black text-gray-900 mt-2">€ {{ number_format($totalSales, 2) }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Across {{ $orderCount }} completed orders</p>
                    <div class="mt-4 h-1 rounded-full bg-gradient-to-r from-sky-400 to-emerald-500"></div>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-lg shadow-sky-100 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-200">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em]">Average order value</p>
                    <h2 class="text-3xl font-black text-gray-900 mt-2">€ {{ number_format($averageOrderValue, 2) }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $orderCount }} orders today</p>
                    <div class="mt-4 h-1 rounded-full bg-gradient-to-r from-sky-400 to-emerald-500"></div>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-lg shadow-sky-100 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-200">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em]">Completed orders</p>
                    <h2 class="text-3xl font-black text-gray-900 mt-2">{{ $orderCount }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Ready for final review</p>
                    <div class="mt-4 h-1 rounded-full bg-gradient-to-r from-sky-400 to-emerald-500"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl shadow-sm p-6 flex flex-col gap-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em]">Channel performance</p>
                            <h3 class="text-lg font-bold text-gray-900">Sales breakdown</h3>
                        </div>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-molveno-blue-50 text-molveno-blue-700">Total € {{ number_format($totalSales, 2) }}</span>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach($salesByType as $channel)
                            <div class="bg-white border border-gray-200 rounded-2xl p-5 flex flex-col gap-3 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em]">{{ $channel['label'] }}</p>
                                        <p class="text-2xl font-bold text-gray-900 mt-1">€ {{ number_format($channel['sales'], 2) }}</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-700">{{ $channel['orders'] }} orders</span>
                                </div>
                                <div class="h-3 rounded-full bg-indigo-50 overflow-hidden">
                                    <span class="block h-full rounded-full {{ $channel['key'] === 'restaurant' ? 'bg-sky-500' : 'bg-emerald-500' }}" style="width: {{ min(100, $channel['share']) }}%"></span>
                                </div>
                                <p class="text-xs text-gray-500">{{ $channel['share'] }}% of daily revenue</p>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em] mb-3">Top dishes</p>
                        <div class="flex flex-col gap-3">
                            @forelse($topItems as $index => $item)
                                <div class="flex items-center justify-between border border-gray-200 rounded-xl p-4 bg-white">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-8 h-8 rounded-full bg-molveno-blue-50 text-molveno-blue-600 font-bold flex items-center justify-center">{{ $index + 1 }}</span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $item['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $item['qty'] }} servings sold</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold bg-emerald-50 text-emerald-700">€ {{ number_format($item['revenue'], 2) }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No sales recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 flex flex-col gap-5">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em]">Recent highlights</p>
                        <h3 class="text-lg font-bold text-gray-900">Latest orders</h3>
                    </div>
                    <div class="flex flex-col gap-4">
                        @foreach($recentOrders as $order)
                            <div class="border border-gray-100 rounded-xl p-4 flex flex-col gap-2">
                                <div class="flex items-center justify-between text-sm font-semibold text-gray-900">
                                    <span>{{ $order['id'] }}</span>
                                    <span class="text-gray-400 text-xs">{{ $order['closed_at'] }}</span>
                                </div>
                                <p class="text-sm text-gray-500">{{ $order['location'] }} &middot; {{ $order['waiter'] }}</p>
                                <div class="flex items-center justify-between text-sm font-semibold">
                                    <span class="text-gray-400">{{ count($order['items']) }} items</span>
                                    <span class="text-primary">€ {{ number_format($order['total'], 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em]">Order ledger</p>
                        <h3 class="text-lg font-bold text-gray-900">Completed orders</h3>
                    </div>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-primary/10 text-primary">{{ $orderCount }} orders logged</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="text-left py-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-slate-400">Order</th>
                                <th class="text-left py-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-slate-400">Location</th>
                                <th class="text-left py-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-slate-400">Waiter</th>
                                <th class="text-center py-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-slate-400">Items</th>
                                <th class="text-right py-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-slate-400">Total</th>
                                <th class="text-right py-2 text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-slate-400">Closed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedOrders as $order)
                                <tr class="border-b border-slate-100 last:border-0">
                                    <td class="py-3 font-semibold text-gray-900">{{ $order['id'] }}</td>
                                    <td class="py-3 text-gray-500">{{ $order['location'] }}</td>
                                    <td class="py-3 text-gray-500">{{ $order['waiter'] }}</td>
                                    <td class="py-3 text-center text-gray-500">{{ count($order['items']) }}</td>
                                    <td class="py-3 text-right font-semibold text-gray-900">€ {{ number_format($order['total'], 2) }}</td>
                                    <td class="py-3 text-right text-gray-500">{{ $order['closed_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
