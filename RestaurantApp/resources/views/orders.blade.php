<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Orders - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.navigation')

        @php
            // Permission checks
            $canViewKitchen = auth()->user()->can('View Kitchen Orders');
            $canViewBar = auth()->user()->can('View Bar Orders');
            $hasBothPermissions = $canViewKitchen && $canViewBar;

            // Get Kitchen Orders data (only if user has permission)
            $allergenConfig = config('restaurant.allergens');
            $kitchenOrders = collect([]);
            $kitchenCountActive = 0;
            $kitchenCountCompleted = 0;
            $kitchenTotalPending = 0;
            $kitchenTotalReady = 0;

            if ($canViewKitchen) {
                try {
                    $dbOrders = \App\Models\Order::with(['items.dish.ingredients', 'floorPlanElement', 'user', 'reservation'])
                        ->whereIn('status', [\App\Enums\OrderStatus::Active->value, \App\Enums\OrderStatus::Completed->value])
                        ->where('paid', false)
                        ->latest()
                        ->get();

                $kitchenOrders = $dbOrders->map(function ($order) {
                    $dishes = $order->items->map(function ($item) {
                        return [
                            'item_id' => $item->id,
                            'name' => $item->dish?->name ?? 'Unknown',
                            'qty' => $item->quantity,
                            'allergens' => $item->dish?->allergens ?? [],
                            'notes' => $item->notes ?? '',
                            'status' => $item->status->value,
                        ];
                    })->all();

                    $statuses = array_column($dishes, 'status');
                    $cntPending = count(array_filter($statuses, fn ($s) => $s === 'pending' || $s === 'preparing'));
                    $cntReady = count(array_filter($statuses, fn ($s) => $s === 'ready'));
                    $cntServed = count(array_filter($statuses, fn ($s) => $s === 'served'));
                    $cntTotal = count($statuses);
                    $overall = $cntTotal > 0 && $cntServed === $cntTotal ? 'completed'
                                : ($cntReady > 0 && $cntPending === 0 ? 'ready' : 'pending');

                    return [
                        'id' => 'ORD-'.str_pad((string) $order->id, 3, '0', STR_PAD_LEFT),
                        'db_id' => $order->id,
                        'type' => 'restaurant',
                        'table' => $order->floorPlanElement?->table_name ?? '—',
                        'room' => null,
                        'time' => $order->created_at?->format('H:i') ?? '—',
                        'waiter' => $order->user?->name ?? '—',
                        'customer' => $order->reservation?->guest_name ?? '—',
                        'dishes' => $dishes,
                        'cnt_pending' => $cntPending,
                        'cnt_ready' => $cntReady,
                        'cnt_served' => $cntServed,
                        'cnt_total' => $cntTotal,
                        'overall' => $overall,
                    ];
                });

                $kitchenCountActive = count($kitchenOrders->filter(fn ($o) => $o['overall'] !== 'completed'));
                $kitchenCountCompleted = count($kitchenOrders->filter(fn ($o) => $o['overall'] === 'completed'));
                $kitchenTotalPending = (int) $kitchenOrders->sum('cnt_pending');
                $kitchenTotalReady = (int) $kitchenOrders->sum('cnt_ready');
                } catch (\Exception $e) {
                    // Handle case when tables don't exist
                }
            }

            // Bar Orders (mock data from BarOrderController) - only if user has permission
            $barOrders = [];
            if ($canViewBar) {
                $barOrders = [
                ['id' => 'ORD-047', 'type' => 'table', 'table' => 'A3', 'time' => '18:32', 'waiter' => 'Sofia R.', 'drinks' => [['name' => 'Aperol Spritz', 'qty' => 2, 'notes' => 'Extra ice', 'status' => 'pending'], ['name' => 'Acqua Minerale', 'qty' => 3, 'notes' => 'Still water, no ice', 'status' => 'pending'], ['name' => 'Espresso Martini', 'qty' => 1, 'notes' => '', 'status' => 'pending']]],
                ['id' => 'ORD-046', 'type' => 'bar', 'table' => null, 'time' => '18:28', 'waiter' => 'Marco D.', 'drinks' => [['name' => 'Negroni', 'qty' => 1, 'notes' => 'Less Campari', 'status' => 'pending'], ['name' => 'Birra alla Spina', 'qty' => 2, 'notes' => '', 'status' => 'pending']]],
                ['id' => 'ORD-045', 'type' => 'table', 'table' => 'B7', 'time' => '18:14', 'waiter' => 'Elena V.', 'drinks' => [['name' => 'Vino Rosso della Casa', 'qty' => 1, 'notes' => '', 'status' => 'served'], ['name' => 'Limoncello', 'qty' => 2, 'notes' => 'Chilled glasses', 'status' => 'served'], ['name' => 'Caffè Americano', 'qty' => 1, 'notes' => '', 'status' => 'served']]],
                ['id' => 'ORD-044', 'type' => 'bar', 'table' => null, 'time' => '18:09', 'waiter' => 'Marco D.', 'drinks' => [['name' => 'Mojito', 'qty' => 2, 'notes' => 'One virgin', 'status' => 'pending'], ['name' => 'Prosecco', 'qty' => 1, 'notes' => '', 'status' => 'pending'], ['name' => 'Succo di Frutta', 'qty' => 1, 'notes' => 'Orange juice only', 'status' => 'served']]],
                ['id' => 'ORD-043', 'type' => 'table', 'table' => 'A12', 'time' => '18:05', 'waiter' => 'Sofia R.', 'drinks' => [['name' => 'Vino Bianco', 'qty' => 2, 'notes' => '', 'status' => 'pending'], ['name' => 'San Pellegrino', 'qty' => 3, 'notes' => 'With lemon slices', 'status' => 'pending']]],
                ['id' => 'ORD-042', 'type' => 'table', 'table' => 'C2', 'time' => '17:58', 'waiter' => 'Elena V.', 'drinks' => [['name' => 'Grappa', 'qty' => 1, 'notes' => '', 'status' => 'served'], ['name' => 'Caffè Affogato', 'qty' => 2, 'notes' => 'Decaf espresso', 'status' => 'served'], ['name' => 'Amaretto Sour', 'qty' => 1, 'notes' => '', 'status' => 'served']]],
                ['id' => 'ORD-041', 'type' => 'bar', 'table' => null, 'time' => '17:45', 'waiter' => 'Marco D.', 'drinks' => [['name' => 'Gin Tonic', 'qty' => 2, 'notes' => 'Hendricks with cucumber', 'status' => 'served'], ['name' => 'Bellini', 'qty' => 1, 'notes' => '', 'status' => 'served']]],
                ['id' => 'ORD-040', 'type' => 'table', 'table' => 'B2', 'time' => '17:38', 'waiter' => 'Sofia R.', 'drinks' => [['name' => 'Campari Soda', 'qty' => 1, 'notes' => '', 'status' => 'pending'], ['name' => 'Chinotto', 'qty' => 2, 'notes' => '', 'status' => 'pending'], ['name' => 'Espresso', 'qty' => 3, 'notes' => 'One decaf', 'status' => 'served']]],
            ];

                foreach ($barOrders as &$order) {
                    $statuses = array_column($order['drinks'], 'status');
                    $order['cnt_pending'] = count(array_filter($statuses, fn ($s) => $s === 'pending'));
                    $order['cnt_ready'] = count(array_filter($statuses, fn ($s) => $s === 'ready'));
                    $order['cnt_served'] = count(array_filter($statuses, fn ($s) => $s === 'served'));
                    $order['cnt_total'] = count($statuses);
                    $order['overall'] = $order['cnt_served'] === $order['cnt_total'] ? 'completed' : ($order['cnt_ready'] > 0 ? 'ready' : 'pending');
                }
                unset($order);

                $barCountActive = count(array_filter($barOrders, fn ($o) => $o['overall'] !== 'completed'));
                $barCountCompleted = count(array_filter($barOrders, fn ($o) => $o['overall'] === 'completed'));
                $barTotalPending = array_sum(array_column($barOrders, 'cnt_pending'));
                $barTotalReady = array_sum(array_column($barOrders, 'cnt_ready'));
            }
        @endphp

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

            <!-- Page Title -->
            <div class="text-center mb-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Orders</h1>
                <p class="text-gray-500 text-sm sm:text-base">
                    @if($hasBothPermissions)
                        Manage kitchen and bar orders in one place
                    @elseif($canViewKitchen)
                        Kitchen orders queue
                    @else
                        Bar orders queue
                    @endif
                </p>
            </div>

            <!-- Mode Toggle Card (only shown when user has both permissions) -->
            @if($hasBothPermissions)
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-500 font-medium">Select Order Type</div>
                    <div class="inline-flex items-center bg-gray-50 border border-gray-200 rounded-2xl p-1.5 shadow-inner">
                        <button type="button" id="btn-kitchen" class="mode-btn px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-200 flex items-center gap-3 bg-molveno-blue-500 text-white shadow-md hover:shadow-lg" onclick="setMode('kitchen')">
                            <span class="bg-white/20 p-1.5 rounded-lg">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/>
                                    <path d="M7 2v20"/>
                                    <path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                                </svg>
                            </span>
                            Kitchen
                        </button>
                        <button type="button" id="btn-bar" class="mode-btn px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-200 flex items-center gap-3 text-gray-600 hover:bg-white hover:shadow-md" onclick="setMode('bar')">
                            <span class="bg-gray-200 p-1.5 rounded-lg">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8 2h8l-4 9v11"/>
                                    <path d="M4 2h16"/>
                                    <path d="M6 22h12"/>
                                </svg>
                            </span>
                            Bar
                        </button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Kitchen Section (only shown if user has kitchen permission) -->
            @if($canViewKitchen)
            <div id="kitchen-panel" class="panel @if($canViewBar) hidden @endif">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sm:p-8">
                    <x-ui.page-header title="Kitchen Orders" subtitle="Live order queue — Molveno Lake Resort">
                        <x-slot:actions>
                            <x-orders.status-summary :totalPending="$kitchenTotalPending" :totalReady="$kitchenTotalReady" :countCompleted="$kitchenCountCompleted" />
                        </x-slot:actions>
                    </x-ui.page-header>

                    <div class="mt-6">
                        <x-orders.filter-tabs :orderCount="$kitchenOrders->count()" :countActive="$kitchenCountActive" :countCompleted="$kitchenCountCompleted" />
                    </div>

                    <div class="mt-6">
                        <x-orders.order-grid>
                            @foreach($kitchenOrders as $order)
                                <x-orders.order-card :order="$order" :allergenConfig="$allergenConfig" />
                            @endforeach
                        </x-orders.order-grid>
                    </div>

                    <div id="kitchen-empty" class="hidden mt-8">
                        <x-ui.empty-state title="No kitchen orders match this filter.">
                            <x-slot:icon>
                                <svg class="text-gray-300 mb-3" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                                </svg>
                            </x-slot:icon>
                        </x-ui.empty-state>
                    </div>
                </div>
            </div>
            @endif

            <!-- Bar Section (only shown if user has bar permission) -->
            @if($canViewBar)
            <div id="bar-panel" class="panel @if($canViewKitchen) hidden @endif">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sm:p-8">
                    <x-ui.page-header title="Bar Orders" subtitle="Live drink queue — Molveno Lake Resort">
                        <x-slot:actions>
                            <x-orders.status-summary :totalPending="$barTotalPending" :totalReady="$barTotalReady" :countCompleted="$barCountCompleted" />
                        </x-slot:actions>
                    </x-ui.page-header>

                    <div class="mt-6">
                        <x-bar-orders.filter-tabs :orderCount="count($barOrders)" :countActive="$barCountActive" :countCompleted="$barCountCompleted" />
                    </div>

                    <div class="mt-6">
                        <x-orders.order-grid>
                            @foreach($barOrders as $order)
                                <x-bar-orders.order-card :order="$order" />
                            @endforeach
                        </x-orders.order-grid>
                    </div>

                    <div id="bar-empty" class="hidden mt-8">
                        <x-ui.empty-state title="No bar orders match this filter.">
                            <x-slot:icon>
                                <svg class="text-gray-300 mb-3" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M8 2h8l-4 9v11"/>
                                    <path d="M4 2h16"/>
                                    <path d="M6 22h12"/>
                                </svg>
                            </x-slot:icon>
                        </x-ui.empty-state>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <x-orders.scripts />
        <x-bar-orders.scripts />

        <script>
            function setMode(mode) {
                // Update buttons (only if they exist)
                const btnKitchen = document.getElementById('btn-kitchen');
                const btnBar = document.getElementById('btn-bar');

                if (btnKitchen && btnBar) {
                    const activeBtn = mode === 'kitchen' ? btnKitchen : btnBar;
                    const inactiveBtn = mode === 'kitchen' ? btnBar : btnKitchen;
                    const activeIcon = activeBtn.querySelector('span');
                    const inactiveIcon = inactiveBtn.querySelector('span');

                    // Active button styles
                    activeBtn.classList.remove('text-gray-600', 'hover:bg-white', 'hover:shadow-md');
                    activeBtn.classList.add('bg-molveno-blue-500', 'text-white', 'shadow-md', 'hover:shadow-lg');
                    if (activeIcon) {
                        activeIcon.classList.remove('bg-gray-200');
                        activeIcon.classList.add('bg-white/20');
                    }

                    // Inactive button styles
                    inactiveBtn.classList.remove('bg-molveno-blue-500', 'text-white', 'shadow-md', 'hover:shadow-lg');
                    inactiveBtn.classList.add('text-gray-600', 'hover:bg-white', 'hover:shadow-md');
                    if (inactiveIcon) {
                        inactiveIcon.classList.remove('bg-white/20');
                        inactiveIcon.classList.add('bg-gray-200');
                    }
                }

                // Show/hide panels (only if they exist)
                const kitchenPanel = document.getElementById('kitchen-panel');
                const barPanel = document.getElementById('bar-panel');

                if (kitchenPanel) {
                    kitchenPanel.classList.toggle('hidden', mode !== 'kitchen');
                }
                if (barPanel) {
                    barPanel.classList.toggle('hidden', mode !== 'bar');
                }

                // Re-initialize tabs for the visible panel
                const panel = document.getElementById(mode + '-panel');
                const defaultTab = panel?.querySelector('button[data-default="true"]') || panel?.querySelector('button[data-tab]');
                if (defaultTab && typeof switchTab === 'function') {
                    switchTab(defaultTab);
                }

                localStorage.setItem('ordersMode', mode);
            }

            // Initialize on load
            document.addEventListener('DOMContentLoaded', () => {
                // Check which panels are available
                const hasKitchen = document.getElementById('kitchen-panel') !== null;
                const hasBar = document.getElementById('bar-panel') !== null;

                // If only one panel exists, don't try to toggle
                if (!hasKitchen && !hasBar) {
                    return; // No panels available
                }

                // Determine available mode
                let defaultMode;
                if (hasKitchen && hasBar) {
                    // Both available: use saved preference or default to kitchen
                    defaultMode = localStorage.getItem('ordersMode') || 'kitchen';
                } else if (hasKitchen) {
                    // Only kitchen available
                    defaultMode = 'kitchen';
                } else {
                    // Only bar available
                    defaultMode = 'bar';
                }

                setMode(defaultMode);
            });
        </script>

        @livewireScripts
    </body>
</html>
