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
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.navigation')

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
                    ['name'=>'Spaghetti Bolognese', 'qty'=>1, 'allergens'=>['gluten','wheat','milk'], 'notes'=>'Extra sauce on the side',                'status'=>'pending'],
                    ['name'=>'Margherita Pizza',     'qty'=>2, 'allergens'=>['gluten','wheat','milk'], 'notes'=>'Well done crust',                        'status'=>'pending'],
                    ['name'=>'Caesar Salad',         'qty'=>1, 'allergens'=>['gluten','milk'],         'notes'=>'No croutons — guest has gluten allergy', 'status'=>'pending'],
                ],
            ],
            [
                'id' => 'ORD-046', 'type' => 'room_service', 'table' => null, 'room' => '204', 'time' => '18:28', 'waiter' => 'Marco D.',
                'dishes' => [
                    ['name'=>'Mushroom Risotto', 'qty'=>1, 'allergens'=>['milk'],  'notes'=>'No parmesan, dairy allergy', 'status'=>'pending'],
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
                    ['name'=>'Pasta Carbonara',         'qty'=>1, 'allergens'=>['gluten','wheat','milk'],       'notes'=>'No guanciale, vegetarian guest', 'status'=>'pending'],
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
                    ['name'=>'Branzino al Forno',         'qty'=>1, 'allergens'=>[],       'notes'=>'Lemon on the side',              'status'=>'pending'],
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
                @php
                    $tabBtnClasses = 'tab-btn inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition hover:border-sky-400 hover:text-sky-700 shadow-sm';
                    $tabCountClasses = 'tab-count inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-[0.65rem] font-bold bg-slate-100 text-slate-500';
                @endphp
                <button class="{{ $tabBtnClasses }} bg-white border-slate-200 text-slate-600" data-tab="all" data-default="true" onclick="switchTab(this)">
                    All
                    <span class="{{ $tabCountClasses }}">{{ count($orders) }}</span>
                </button>
                <button class="{{ $tabBtnClasses }} bg-white border-slate-200 text-slate-600" data-tab="active" onclick="switchTab(this)">
                    Active
                    <span class="{{ $tabCountClasses }}">{{ $countActive }}</span>
                </button>
                <button class="{{ $tabBtnClasses }} bg-white border-slate-200 text-slate-600" data-tab="completed" onclick="switchTab(this)">
                    Completed
                    <span class="{{ $tabCountClasses }}">{{ $countCompleted }}</span>
                </button>
                <div class="w-px h-5 bg-gray-200 mx-1 hidden sm:block"></div>
                <button class="{{ $tabBtnClasses }} bg-white border-slate-200 text-slate-600" data-tab="restaurant" onclick="switchTab(this)">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                    </svg>
                    Restaurant
                </button>
                <button class="{{ $tabBtnClasses }} bg-white border-slate-200 text-slate-600" data-tab="room_service" onclick="switchTab(this)">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 4v16"/><path d="M22 8H2"/><path d="M22 20V8l-8-4H2"/>
                    </svg>
                    Room Service
                </button>
            </div>

            <!-- ── KDS order grid ─────────────────────────────── -->
            <div class="grid gap-4 items-start xl:grid-cols-4 lg:grid-cols-3 sm:grid-cols-2 grid-cols-1" id="order-list">
                @foreach($orders as $order)
                    <x-orders.order-card :order="$order" :allergenConfig="$allergenConfig" />
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
            const TAB_ACTIVE_CLASSES = ['bg-sky-700', 'border-sky-700', 'text-white', 'shadow'];
            const TAB_INACTIVE_CLASSES = ['bg-white', 'border-slate-200', 'text-slate-600', 'shadow-sm'];
            const TAB_COUNT_ACTIVE_CLASSES = ['bg-white/30', 'text-white'];
            const TAB_COUNT_INACTIVE_CLASSES = ['bg-slate-100', 'text-slate-500'];

            document.addEventListener('DOMContentLoaded', () => {
                const defaultTab = document.querySelector('.tab-btn[data-default="true"]') || document.querySelector('.tab-btn');
                if (defaultTab) {
                    switchTab(defaultTab);
                }
                document.querySelectorAll('.mark-ready-btn').forEach(btn => {
                    setMarkReadyAppearance(btn, btn.dataset.dishStatus === 'ready' ? 'ready' : 'pending');
                });
                document.querySelectorAll('.order-card').forEach(card => {
                    updateOrderSendState(card);
                    syncCardVisualState(card);
                });
                document.addEventListener('click', handleActionClick);
            });

            const CARD_COMPLETED_CLASSES = ['bg-emerald-50', 'border-emerald-200', 'shadow-md'];
            const CARD_DEFAULT_CLASSES = ['bg-white', 'border-gray-200', 'shadow-sm'];

            function switchTab(btn) {
                const tab = btn.dataset.tab;
                document.querySelectorAll('.tab-btn').forEach(b => setTabAppearance(b, b === btn));
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

            function handleActionClick(event) {
                const target = event.target instanceof Element ? event.target : event.target.parentElement;
                if (!target) return;

                const markBtn = target.closest('.mark-ready-btn');
                if (markBtn) {
                    event.preventDefault();
                    markDishReady(markBtn);
                    return;
                }

                const sendBtn = target.closest('.order-send-btn');
                if (sendBtn) {
                    event.preventDefault();
                    if (!sendBtn.disabled) {
                        completeOrder(sendBtn);
                    }
                }
            }

            function markDishReady(button) {
                const nextState = button.dataset.dishStatus === 'ready' ? 'pending' : 'ready';
                setMarkReadyAppearance(button, nextState);
                button.dataset.dishStatus = nextState;

                const label = button.querySelector('.mark-ready-label');
                if (label) label.textContent = nextState === 'ready' ? 'Ready' : 'Mark Ready';

                const dishAction = button.closest('.dish-action');
                updateDishVisualState(dishAction, nextState);

                updateOrderSendState(button.closest('.order-card'));
            }

            function completeOrder(button) {
                setSendButtonState(button, 'sent');
                const card = button.closest('.order-card');
                if (card) {
                    card.dataset.overall = 'completed';
                    hideOrderActions(card);
                    syncCardVisualState(card);
                }
            }

            function updateOrderSendState(card) {
                if (!card) return;

                const dishActions = Array.from(card.querySelectorAll('.dish-action'));
                const hasPending = dishActions.some(action => action.dataset.dishStatus === 'pending');
                const allReadyOrServed = dishActions.length > 0 && dishActions.every(action => ['ready', 'served'].includes(action.dataset.dishStatus));

                const sendBtn = card.querySelector('.order-send-btn');

                if (sendBtn) {
                    const currentState = card.dataset.overall === 'completed' ? 'sent'
                                        : allReadyOrServed ? 'ready'
                                        : 'disabled';
                    setSendButtonState(sendBtn, currentState);
                }

                if (card.dataset.overall !== 'completed') {
                    card.dataset.overall = allReadyOrServed ? 'ready' : (hasPending ? 'pending' : card.dataset.overall);
                }

                if (card.dataset.overall === 'completed') {
                    hideOrderActions(card);
                } else {
                    showOrderActions(card);
                }

                syncCardVisualState(card);
            }

            function setTabAppearance(button, isActive) {
                if (!button) return;
                button.classList.remove(...TAB_ACTIVE_CLASSES, ...TAB_INACTIVE_CLASSES);
                button.classList.add(...(isActive ? TAB_ACTIVE_CLASSES : TAB_INACTIVE_CLASSES));

                const count = button.querySelector('.tab-count');
                if (count) {
                    count.classList.remove(...TAB_COUNT_ACTIVE_CLASSES, ...TAB_COUNT_INACTIVE_CLASSES);
                    count.classList.add(...(isActive ? TAB_COUNT_ACTIVE_CLASSES : TAB_COUNT_INACTIVE_CLASSES));
                }
            }

            function updateDishVisualState(dishAction, state) {
                if (!dishAction) return;
                dishAction.dataset.dishStatus = state;

                const wrapper = dishAction.closest('[data-dish-wrapper]');
                if (wrapper) {
                    wrapper.dataset.dishStatus = state;
                    const dot = wrapper.querySelector('[data-role="status-dot"]');
                    if (dot) {
                        setStatusDotAppearance(dot, state);
                    }
                }
            }

            function setMarkReadyAppearance(button, state) {
                if (!button) return;
                const pending = parseClasses(button.dataset.classPending);
                const ready = parseClasses(button.dataset.classReady);
                button.classList.remove(...pending, ...ready);

                if (state === 'ready') {
                    button.classList.add(...ready);
                } else {
                    button.classList.add(...pending);
                }
            }

            function setStatusDotAppearance(dot, state) {
                if (!dot) return;
                const pending = parseClasses(dot.dataset.classPending);
                const ready = parseClasses(dot.dataset.classReady);
                const served = parseClasses(dot.dataset.classServed);
                dot.classList.remove(...pending, ...ready, ...served);

                if (state === 'ready') {
                    dot.classList.add(...ready);
                } else if (state === 'served') {
                    dot.classList.add(...served);
                } else {
                    dot.classList.add(...pending);
                }

                dot.dataset.status = state;
            }

            function setSendButtonState(button, state) {
                if (!button) return;
                const disabled = parseClasses(button.dataset.classDisabled);
                const ready = parseClasses(button.dataset.classReady);
                const sent = parseClasses(button.dataset.classSent);
                button.classList.remove(...disabled, ...ready, ...sent);

                if (state === 'sent') {
                    button.classList.add(...sent);
                    button.disabled = true;
                } else if (state === 'ready') {
                    button.classList.add(...ready);
                    button.disabled = false;
                } else {
                    button.classList.add(...disabled);
                    button.disabled = true;
                }

                button.dataset.sendState = state;
                const label = button.querySelector('.order-send-label');
                if (label) label.textContent = state === 'sent' ? 'Sent' : 'Send Out';
            }

            function syncCardVisualState(card) {
                if (!card) return;
                card.classList.remove(...CARD_COMPLETED_CLASSES, ...CARD_DEFAULT_CLASSES);
                if (card.dataset.overall === 'completed') {
                    card.classList.add(...CARD_COMPLETED_CLASSES);
                } else {
                    card.classList.add(...CARD_DEFAULT_CLASSES);
                }
            }

            function hideOrderActions(card) {
                const actions = card.querySelector('[data-role="order-actions"]');
                if (actions) actions.classList.add('hidden');
            }

            function showOrderActions(card) {
                const actions = card.querySelector('[data-role="order-actions"]');
                if (actions) actions.classList.remove('hidden');
            }

            function parseClasses(value) {
                return (value || '').split(' ').filter(Boolean);
            }
        </script>
    @livewireScripts
    </body>
</html>
