<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Kitchen Orders - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            /* ── KDS grid ────────────────────────────────────── */
            .order-grid {
                display: grid;
                gap: 1rem;
                align-items: start;
                grid-template-columns: repeat(4, 1fr);
            }
            @media (max-width: 1279px) { .order-grid { grid-template-columns: repeat(3, 1fr); } }
            @media (max-width: 767px)  { .order-grid { grid-template-columns: repeat(2, 1fr); } }
            @media (max-width: 479px)  { .order-grid { grid-template-columns: 1fr; } }

            /* ── Filter tabs ──────────────────────────────────── */
            .tab-btn {
                display: inline-flex; align-items: center; gap: 0.375rem;
                padding: 0.375rem 0.875rem; border-radius: 9999px;
                font-size: 0.8125rem; font-weight: 600;
                border: 1px solid #e5e7eb; background: #fff; color: #6b7280;
                cursor: pointer; transition: border-color .15s, background .15s, color .15s;
                font-family: inherit; white-space: nowrap;
            }
            .tab-btn:hover { border-color: #309bcf; color: #005693; }
            .tab-btn.tab-active { background: #005693; border-color: #005693; color: #fff; }
            .tab-count {
                display: inline-flex; align-items: center; justify-content: center;
                min-width: 1.125rem; height: 1.125rem; padding: 0 0.25rem;
                border-radius: 9999px; font-size: 0.6875rem; font-weight: 700;
                background: #e5e7eb; color: #4b5563;
            }
            .tab-active .tab-count { background: rgba(255,255,255,.25); color: #fff; }

            /* ── Notes textarea ───────────────────────────────── */
            .note-area {
                width: 100%;
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 0.375rem;
                padding: 0.375rem 0.5rem;
                font-size: 0.75rem;
                color: #374151;
                font-family: inherit;
                resize: none;
                outline: none;
                transition: border-color .15s, box-shadow .15s;
                line-height: 1.45;
            }
            .note-area:focus {
                border-color: #309bcf;
                box-shadow: 0 0 0 3px rgba(48,155,207,.15);
            }
            .note-area::placeholder { color: #9ca3af; }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.guest-navigation')

        @php
        $allergenConfig = [
            'gluten' => ['label'=>'Gluten', 'bg'=>'#D97706', 'icon'=>'<path fill="white" d="M8 1.5C6.5 3 5 5.5 5 7.5c0 1 .4 1.9 1 2.6V14h4V10.1c.6-.7 1-1.6 1-2.6 0-2-1.5-4.5-3-6z"/>'],
            'nuts'   => ['label'=>'Nuts',   'bg'=>'#92400E', 'icon'=>'<ellipse cx="8" cy="9.5" rx="5" ry="5.5" fill="white"/><path d="M5.5 5C5.5 3.3 6.6 2 8 2s2.5 1.3 2.5 3" stroke="#92400E" stroke-width="1" fill="none" stroke-linecap="round"/>'],
            'milk'   => ['label'=>'Milk',   'bg'=>'#0284C7', 'icon'=>'<path fill="white" d="M6 2h4l.5 2.5H5.5L6 2zM5 5h6l-1 9H6L5 5z"/>'],
            'wheat'  => ['label'=>'Wheat',  'bg'=>'#CA8A04', 'icon'=>'<line x1="8" y1="14" x2="8" y2="4" stroke="white" stroke-width="1.5"/><ellipse cx="5.5" cy="6" rx="2.5" ry="1.5" fill="white" transform="rotate(-20 5.5 6)"/><ellipse cx="10.5" cy="6" rx="2.5" ry="1.5" fill="white" transform="rotate(20 10.5 6)"/><ellipse cx="5" cy="9" rx="2.5" ry="1.5" fill="white" transform="rotate(-20 5 9)"/><ellipse cx="11" cy="9" rx="2.5" ry="1.5" fill="white" transform="rotate(20 11 9)"/><ellipse cx="8" cy="3" rx="1.5" ry="2" fill="white"/>'],
        ];

        $orders = [
            [
                'id' => 'ORD-047', 'type' => 'restaurant', 'table' => 'A3',  'room' => null,  'time' => '18:32', 'waiter' => 'Sofia R.',
                'dishes' => [
                    ['name'=>'Spaghetti Bolognese', 'qty'=>1, 'allergens'=>['gluten','wheat','milk'], 'notes'=>'Extra sauce on the side',                'status'=>'ready'],
                    ['name'=>'Margherita Pizza',     'qty'=>2, 'allergens'=>['gluten','wheat','milk'], 'notes'=>'Well done crust',                        'status'=>'pending'],
                    ['name'=>'Caesar Salad',         'qty'=>1, 'allergens'=>['gluten','milk'],         'notes'=>'No croutons — guest has gluten allergy', 'status'=>'pending'],
                ],
            ],
            [
                'id' => 'ORD-046', 'type' => 'room_service', 'table' => null, 'room' => '204', 'time' => '18:28', 'waiter' => 'Marco D.',
                'dishes' => [
                    ['name'=>'Mushroom Risotto', 'qty'=>1, 'allergens'=>['milk'],  'notes'=>'No parmesan, dairy allergy', 'status'=>'ready'],
                    ['name'=>'Panna Cotta',       'qty'=>1, 'allergens'=>['milk'],  'notes'=>'',                          'status'=>'pending'],
                    ['name'=>'Acqua Minerale',    'qty'=>2, 'allergens'=>[],        'notes'=>'Still water, no ice',       'status'=>'served'],
                ],
            ],
            [
                'id' => 'ORD-045', 'type' => 'restaurant', 'table' => 'B7',  'room' => null,  'time' => '18:14', 'waiter' => 'Elena V.',
                'dishes' => [
                    ['name'=>'Grilled Salmon',        'qty'=>2, 'allergens'=>[], 'notes'=>'One medium, one well done', 'status'=>'served'],
                    ['name'=>'Beef Tenderloin',        'qty'=>1, 'allergens'=>[], 'notes'=>'Medium rare',               'status'=>'served'],
                    ['name'=>'Verdure Grigliate',      'qty'=>3, 'allergens'=>[], 'notes'=>'',                          'status'=>'served'],
                    ['name'=>'Vino Rosso della Casa',  'qty'=>1, 'allergens'=>[], 'notes'=>'',                          'status'=>'served'],
                ],
            ],
            [
                'id' => 'ORD-044', 'type' => 'room_service', 'table' => null, 'room' => '118', 'time' => '18:09', 'waiter' => 'Marco D.',
                'dishes' => [
                    ['name'=>'Bruschetta al Pomodoro', 'qty'=>1, 'allergens'=>['gluten','wheat'],              'notes'=>'',                               'status'=>'served'],
                    ['name'=>'Pasta Carbonara',         'qty'=>1, 'allergens'=>['gluten','wheat','milk'],       'notes'=>'No guanciale, vegetarian guest', 'status'=>'ready'],
                    ['name'=>'Tiramisu',                'qty'=>2, 'allergens'=>['gluten','wheat','milk','nuts'],'notes'=>'Nut allergy — check recipe!',    'status'=>'pending'],
                ],
            ],
            [
                'id' => 'ORD-043', 'type' => 'restaurant', 'table' => 'A12', 'room' => null,  'time' => '18:05', 'waiter' => 'Sofia R.',
                'dishes' => [
                    ['name'=>'Antipasto Misto',  'qty'=>1, 'allergens'=>['milk'], 'notes'=>'No olives',                    'status'=>'pending'],
                    ['name'=>'Osso Buco',         'qty'=>2, 'allergens'=>[],       'notes'=>'',                             'status'=>'pending'],
                    ['name'=>'Polenta e Funghi',  'qty'=>1, 'allergens'=>['milk'], 'notes'=>'Dairy-free alternative please','status'=>'pending'],
                    ['name'=>'Patate al Forno',   'qty'=>2, 'allergens'=>[],       'notes'=>'Extra crispy',                 'status'=>'pending'],
                ],
            ],
            [
                'id' => 'ORD-042', 'type' => 'restaurant', 'table' => 'C2',  'room' => null,  'time' => '17:58', 'waiter' => 'Elena V.',
                'dishes' => [
                    ['name'=>'Caprese Salad',             'qty'=>2, 'allergens'=>['milk'], 'notes'=>'Extra basil',                    'status'=>'served'],
                    ['name'=>'Risotto ai Frutti di Mare', 'qty'=>1, 'allergens'=>['milk'], 'notes'=>'',                               'status'=>'served'],
                    ['name'=>'Branzino al Forno',         'qty'=>1, 'allergens'=>[],       'notes'=>'Lemon on the side',              'status'=>'ready'],
                    ['name'=>'Gelato al Limone',          'qty'=>3, 'allergens'=>[],       'notes'=>'One scoop only for table guest', 'status'=>'pending'],
                ],
            ],
            [
                'id' => 'ORD-041', 'type' => 'room_service', 'table' => null, 'room' => '312', 'time' => '17:45', 'waiter' => 'Marco D.',
                'dishes' => [
                    ['name'=>'Vegan Buddha Bowl',     'qty'=>1, 'allergens'=>[],               'notes'=>'No sesame seeds',   'status'=>'served'],
                    ['name'=>'Focaccia al Rosmarino', 'qty'=>1, 'allergens'=>['gluten','wheat'],'notes'=>'',                 'status'=>'served'],
                    ['name'=>'Succo di Frutta',       'qty'=>2, 'allergens'=>[],               'notes'=>'Orange juice only', 'status'=>'served'],
                ],
            ],
            [
                'id' => 'ORD-040', 'type' => 'restaurant', 'table' => 'B2',  'room' => null,  'time' => '17:38', 'waiter' => 'Sofia R.',
                'dishes' => [
                    ['name'=>'Minestrone Soup',       'qty'=>2, 'allergens'=>[],      'notes'=>'Extra bread on the side', 'status'=>'served'],
                    ['name'=>'Pollo alla Cacciatora', 'qty'=>2, 'allergens'=>[],      'notes'=>'',                        'status'=>'served'],
                    ['name'=>'Caffè Affogato',        'qty'=>2, 'allergens'=>['milk'],'notes'=>'Decaf espresso',          'status'=>'served'],
                ],
            ],
        ];

        foreach ($orders as &$order) {
            $statuses             = array_column($order['dishes'], 'status');
            $order['cnt_pending'] = count(array_filter($statuses, fn($s) => $s === 'pending'));
            $order['cnt_ready']   = count(array_filter($statuses, fn($s) => $s === 'ready'));
            $order['cnt_served']  = count(array_filter($statuses, fn($s) => $s === 'served'));
            $order['cnt_total']   = count($statuses);
            $order['overall']     = $order['cnt_served'] === $order['cnt_total'] ? 'completed'
                                  : ($order['cnt_ready'] > 0 ? 'ready' : 'pending');
        }
        unset($order);

        $countActive    = count(array_filter($orders, fn($o) => $o['overall'] !== 'completed'));
        $countCompleted = count(array_filter($orders, fn($o) => $o['overall'] === 'completed'));
        $totalPending   = array_sum(array_column($orders, 'cnt_pending'));
        $totalReady     = array_sum(array_column($orders, 'cnt_ready'));
        @endphp

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5">

            <!-- ── Page header ───────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Kitchen Orders</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Live order queue &mdash; Molveno Lake Resort</p>
                </div>
                <div class="flex items-center gap-5 text-sm">
                    <span class="flex items-center gap-1.5 text-gray-500">
                        <span class="w-2 h-2 rounded-full bg-gray-300 shrink-0"></span>
                        {{ $totalPending }} preparing
                    </span>
                    <span class="flex items-center gap-1.5 text-amber-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                        {{ $totalReady }} ready
                    </span>
                    <span class="flex items-center gap-1.5 text-green-600">
                        <span class="w-2 h-2 rounded-full bg-green-400 shrink-0"></span>
                        {{ $countCompleted }} done
                    </span>
                </div>
            </div>

            <!-- ── Filter tabs ────────────────────────────────── -->
            <div class="flex items-center gap-2 flex-wrap">
                <button class="tab-btn tab-active" data-tab="all"          onclick="switchTab(this)">All <span class="tab-count">{{ count($orders) }}</span></button>
                <button class="tab-btn"            data-tab="active"       onclick="switchTab(this)">Active <span class="tab-count">{{ $countActive }}</span></button>
                <button class="tab-btn"            data-tab="completed"    onclick="switchTab(this)">Completed <span class="tab-count">{{ $countCompleted }}</span></button>
                <div class="w-px h-5 bg-gray-200 mx-1 hidden sm:block"></div>
                <button class="tab-btn"            data-tab="restaurant"   onclick="switchTab(this)">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                    </svg>
                    Restaurant
                </button>
                <button class="tab-btn"            data-tab="room_service" onclick="switchTab(this)">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 4v16"/><path d="M22 8H2"/><path d="M22 20V8l-8-4H2"/>
                    </svg>
                    Room Service
                </button>
            </div>

            <!-- ── KDS order grid ─────────────────────────────── -->
            <div class="order-grid" id="order-list">
                @foreach($orders as $order)
                    @php
                        $isRoom = $order['type'] === 'room_service';

                        $headerBg = $order['overall'] === 'completed' ? '#16a34a'
                                  : ($order['overall'] === 'ready'    ? '#d97706' : '#0084c4');
                    @endphp

                    <div class="order-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col"
                         data-overall="{{ $order['overall'] }}"
                         data-type="{{ $order['type'] }}">

                        <!-- ── Ticket header ──────────────────── -->
                        <div class="px-4 py-3 text-white" style="background-color: {{ $headerBg }}">
                            <div class="flex items-start justify-between gap-2">
                                <!-- Left: location + order ID -->
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <!-- Location pill -->
                                        <span class="inline-flex items-center gap-1 text-xs font-bold bg-black/20 px-2 py-0.5 rounded">
                                            @if($isRoom)
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16"/><path d="M22 8H2"/><path d="M22 20V8l-8-4H2"/></svg>
                                                Room {{ $order['room'] }}
                                            @else
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                                                Table {{ $order['table'] }}
                                            @endif
                                        </span>
                                        <span class="text-sm font-bold tracking-wide">{{ $order['id'] }}</span>
                                    </div>
                                    <p class="text-xs opacity-70 mt-0.5">{{ $order['waiter'] }}</p>
                                </div>

                                <!-- Right: time + summary -->
                                <div class="text-right shrink-0">
                                    <p class="text-xs font-semibold">{{ $order['time'] }}</p>
                                    <p class="text-xs opacity-70 mt-0.5">
                                        @if($order['overall'] === 'completed')
                                            All served
                                        @else
                                            @if($order['cnt_ready'] > 0)
                                                {{ $order['cnt_ready'] }} ready &middot;
                                            @endif
                                            @if($order['cnt_pending'] > 0)
                                                {{ $order['cnt_pending'] }} preparing
                                            @endif
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ── Dish list ──────────────────────── -->
                        <div class="flex-1 divide-y divide-gray-100">
                            @foreach($order['dishes'] as $dish)
                                <div class="px-4 py-3 flex flex-col gap-2">

                                    <!-- Dish name row -->
                                    <div class="flex items-start gap-2">
                                        <!-- Status dot -->
                                        @php $dotClass = $dish['status'] === 'served' ? 'bg-green-400' : ($dish['status'] === 'ready' ? 'bg-amber-400' : 'bg-gray-300'); @endphp
                                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $dotClass }}"></span>

                                        <!-- Name + qty -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-baseline justify-between gap-1">
                                                <span class="text-sm font-semibold text-gray-800 leading-snug">{{ $dish['name'] }}</span>
                                                <span class="text-xs text-gray-400 font-medium shrink-0">&times;{{ $dish['qty'] }}</span>
                                            </div>

                                            <!-- Allergens -->
                                            @if(!empty($dish['allergens']))
                                                <div class="flex items-center gap-1 flex-wrap mt-1">
                                                    @foreach($dish['allergens'] as $allergen)
                                                        @if(isset($allergenConfig[$allergen]))
                                                            @php $ac = $allergenConfig[$allergen]; @endphp
                                                            <div title="{{ $ac['label'] }}"
                                                                 class="w-4 h-4 rounded-full flex items-center justify-center shrink-0"
                                                                 style="background-color:{{ $ac['bg'] }}">
                                                                <svg viewBox="0 0 16 16" width="9" height="9">{!! $ac['icon'] !!}</svg>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Notes textarea -->
                                    <textarea class="note-area" rows="2"
                                              placeholder="No notes…">{{ $dish['notes'] }}</textarea>

                                    <!-- Action buttons -->
                                    @if($dish['status'] === 'pending')
                                        <div class="flex gap-1.5">
                                            <button class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg
                                                           bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white transition-colors">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                Mark Ready
                                            </button>
                                            <button disabled class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg
                                                                    bg-gray-50 border border-gray-200 text-gray-300 cursor-not-allowed">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                                Sent Out
                                            </button>
                                        </div>
                                    @elseif($dish['status'] === 'ready')
                                        <div class="flex gap-1.5">
                                            <button disabled class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-medium px-2.5 py-1.5 rounded-lg
                                                                    bg-gray-50 border border-gray-200 text-gray-300 cursor-not-allowed">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                Mark Ready
                                            </button>
                                            <button class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg
                                                           bg-green-600 hover:bg-green-700 text-white transition-colors">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                                Sent Out
                                            </button>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1 text-xs font-medium text-green-600">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                            Served
                                        </div>
                                    @endif

                                </div>
                            @endforeach
                        </div>

                        <!-- ── Card footer ────────────────────── -->
                        <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-gray-400">
                                {{ $order['cnt_total'] }} {{ $order['cnt_total'] === 1 ? 'dish' : 'dishes' }}
                                &middot; {{ $order['cnt_served'] }} served
                            </span>
                            @php $overallClass = $order['overall'] === 'completed' ? 'text-green-600' : ($order['overall'] === 'ready' ? 'text-amber-600' : 'text-gray-400'); @endphp
                            <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $overallClass }}">
                                @if($order['overall'] === 'completed')
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    Completed
                                @elseif($order['overall'] === 'ready')
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    Ready to serve
                                @else
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 1.5"/></svg>
                                    Preparing
                                @endif
                            </span>
                        </div>

                    </div><!-- /order-card -->
                @endforeach
            </div>

            <!-- Empty state -->
            <div id="no-orders" class="hidden flex flex-col items-center py-16 text-center">
                <svg class="text-gray-300 mb-3" width="44" height="44" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                </svg>
                <p class="text-sm font-semibold text-gray-500">No orders match this filter.</p>
            </div>

        </div>

        <script>
            function switchTab(btn) {
                const tab = btn.dataset.tab;
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('tab-active', b === btn));
                let visible = 0;
                document.querySelectorAll('.order-card').forEach(card => {
                    const overall = card.dataset.overall;
                    const type    = card.dataset.type;
                    const show    = tab === 'all'          ? true
                                  : tab === 'active'       ? overall !== 'completed'
                                  : tab === 'completed'    ? overall === 'completed'
                                  : tab === 'restaurant'   ? type === 'restaurant'
                                  : tab === 'room_service' ? type === 'room_service'
                                  : true;
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;
                });
                document.getElementById('no-orders').classList.toggle('hidden', visible > 0);
            }
        </script>
    </body>
</html>
