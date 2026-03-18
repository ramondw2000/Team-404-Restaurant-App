<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Order Management - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            /* ── Filter pills ─────────────────────────────────── */
            .filter-btn {
                padding: 0.35rem 1rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                border: 1px solid #e5e7eb;
                background: #fff;
                color: #374151;
                cursor: pointer;
                transition: border-color .15s, background .15s, color .15s;
                white-space: nowrap;
                font-family: inherit;
            }
            .filter-btn:hover { border-color: #309bcf; color: #005693; }
            .filter-btn.filter-active { background: #005693; border-color: #005693; color: #fff; }
            .filter-btn.filter-active .diet-icon-veg  { background: #fff !important; }
            .filter-btn.filter-active .diet-icon-vegan { background: #fff !important; }

            /* ── Dish card ────────────────────────────────────── */
            .dish-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 0.75rem;
                display: flex;
                align-items: stretch;
                overflow: hidden;
                transition: box-shadow .15s, border-color .15s;
                cursor: default;
            }
            .dish-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); border-color: #bfdbfe; }

            .dish-card-body {
                flex: 1;
                padding: 0.875rem 1rem;
                min-width: 0;
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .dish-card-image {
                width: 108px;
                min-width: 108px;
                position: relative;
                background: #f3f4f6;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            @media (max-width: 479px) {
                .dish-card-image { width: 88px; min-width: 88px; }
            }

            .dish-card-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            /* ── Add button — lives inside the image container ── */
            .btn-add-dish {
                position: absolute;
                bottom: 0.5rem;
                right: 0.5rem;
                width: 2rem;
                height: 2rem;
                border-radius: 9999px;
                background: #0084c4;
                color: #fff;
                border: 2px solid #fff;
                box-shadow: 0 2px 8px rgba(0,84,147,.35);
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: background .15s, transform .1s;
                flex-shrink: 0;
            }
            .btn-add-dish:hover { background: #006ead; transform: scale(1.08); }

            /* ── Qty badge on card ───────────────────────────── */
            .qty-badge {
                position: absolute;
                top: 0.4rem;
                right: 0.4rem;
                min-width: 1.25rem;
                height: 1.25rem;
                padding: 0 0.3rem;
                border-radius: 9999px;
                background: #005693;
                color: #fff;
                font-size: 0.625rem;
                font-weight: 800;
                display: none;
                align-items: center;
                justify-content: center;
                box-shadow: 0 1px 4px rgba(0,0,0,.25);
            }
            .qty-badge.visible { display: flex; }

            /* ── Note area ───────────────────────────────────── */
            .note-area {
                width: 100%;
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 0.5rem;
                padding: 0.375rem 0.625rem;
                font-size: 0.75rem;
                color: #374151;
                font-family: inherit;
                resize: none;
                outline: none;
                transition: border-color .15s, box-shadow .15s;
                line-height: 1.5;
            }
            .note-area:focus { border-color: #309bcf; box-shadow: 0 0 0 3px rgba(48,155,207,.15); }
            .note-area::placeholder { color: #9ca3af; }

            /* ── Qty stepper ─────────────────────────────────── */
            .qty-btn {
                width: 1.625rem;
                height: 1.625rem;
                border-radius: 9999px;
                border: 1.5px solid #0084c4;
                color: #0084c4;
                background: #fff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: background .12s, color .12s;
                flex-shrink: 0;
                font-family: inherit;
            }
            .qty-btn:hover { background: #0084c4; color: #fff; }

            /* ── Sticky order bar ────────────────────────────── */
            #order-bar {
                transition: transform .3s cubic-bezier(.4,0,.2,1), opacity .3s;
            }
            #order-bar.hidden-bar {
                transform: translateY(100%);
                opacity: 0;
                pointer-events: none;
            }

            /* ── Add-dish overlay ────────────────────────────── */
            .add-overlay {
                opacity: 0;
                pointer-events: none;
                transition: opacity .2s ease;
            }
            .add-overlay.open { opacity: 1; pointer-events: auto; }
            .add-modal {
                transform: translateY(16px) scale(.98);
                transition: transform .2s ease, opacity .2s ease;
                opacity: 0;
            }
            .add-overlay.open .add-modal { transform: translateY(0) scale(1); opacity: 1; }

            /* ── Review screen (full-viewport slide-up) ─────── */
            #review-screen {
                transform: translateY(100%);
                transition: transform .35s cubic-bezier(.32,.72,0,1);
            }
            #review-screen.open {
                transform: translateY(0);
            }

            /* ── Toast ───────────────────────────────────────── */
            #toast {
                transition: opacity .3s, transform .3s;
                opacity: 0;
                transform: translateY(-8px);
                pointer-events: none;
            }
            #toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }

            /* ── Scrollbar hide (for filter row) ─────────────── */
            .scrollbar-hide::-webkit-scrollbar { display: none; }
            .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.navigation')

        @php
        /* ── Allergen config ─────────────────────────────────── */
        $allergenConfig = [
            'gluten' => ['label'=>'Gluten', 'bg'=>'#D97706', 'icon'=>'<path fill="white" d="M8 1.5C6.5 3 5 5.5 5 7.5c0 1 .4 1.9 1 2.6V14h4V10.1c.6-.7 1-1.6 1-2.6 0-2-1.5-4.5-3-6z"/>'],
            'nuts'   => ['label'=>'Nuts',   'bg'=>'#92400E', 'icon'=>'<ellipse cx="8" cy="9.5" rx="5" ry="5.5" fill="white"/><path d="M5.5 5C5.5 3.3 6.6 2 8 2s2.5 1.3 2.5 3" stroke="#92400E" stroke-width="1" fill="none" stroke-linecap="round"/>'],
            'milk'   => ['label'=>'Milk',   'bg'=>'#0284C7', 'icon'=>'<path fill="white" d="M6 2h4l.5 2.5H5.5L6 2zM5 5h6l-1 9H6L5 5z"/>'],
            'wheat'  => ['label'=>'Wheat',  'bg'=>'#CA8A04', 'icon'=>'<line x1="8" y1="14" x2="8" y2="4" stroke="white" stroke-width="1.5"/><ellipse cx="5.5" cy="6" rx="2.5" ry="1.5" fill="white" transform="rotate(-20 5.5 6)"/><ellipse cx="10.5" cy="6" rx="2.5" ry="1.5" fill="white" transform="rotate(20 10.5 6)"/><ellipse cx="5" cy="9" rx="2.5" ry="1.5" fill="white" transform="rotate(-20 5 9)"/><ellipse cx="11" cy="9" rx="2.5" ry="1.5" fill="white" transform="rotate(20 11 9)"/><ellipse cx="8" cy="3" rx="1.5" ry="2" fill="white"/>'],
            'fish'   => ['label'=>'Fish',   'bg'=>'#0891B2', 'icon'=>'<path fill="white" d="M2 8c2-3 5-4 8-4s6 1 8 4c-2 3-5 4-8 4S4 11 2 8z"/><circle cx="13" cy="8" r="1.2" fill="#0891B2"/>'],
            'egg'    => ['label'=>'Egg',    'bg'=>'#7C3AED', 'icon'=>'<ellipse cx="8" cy="9" rx="5" ry="6" fill="white"/><ellipse cx="8" cy="10" rx="2.5" ry="3" fill="#7C3AED"/>'],
        ];

        /* ── Menu ────────────────────────────────────────────── */
        $dishes = [
            /* Starters */
            ['id'=>1,  'name'=>'Bruschetta al Pomodoro',  'price'=>7.00,  'cat'=>'Starters', 'desc'=>'Toasted bread with tomato, garlic and basil',           'allergens'=>['gluten','wheat'],              'dietary'=>['vegan']],
            ['id'=>2,  'name'=>'Caprese Salad',            'price'=>9.00,  'cat'=>'Starters', 'desc'=>'Fresh mozzarella, tomato and basil with olive oil',     'allergens'=>['milk'],                        'dietary'=>['vegetarian']],
            ['id'=>3,  'name'=>'Caesar Salad',             'price'=>10.50, 'cat'=>'Starters', 'desc'=>'Romaine lettuce, parmesan, croutons and Caesar dressing','allergens'=>['gluten','milk'],              'dietary'=>[]],
            ['id'=>4,  'name'=>'Antipasto Misto',          'price'=>12.00, 'cat'=>'Starters', 'desc'=>'Selection of cured meats, cheeses and marinated vegetables','allergens'=>['milk'],                   'dietary'=>[]],
            ['id'=>5,  'name'=>'Minestrone Soup',          'price'=>7.00,  'cat'=>'Starters', 'desc'=>'Hearty vegetable soup with seasonal produce',           'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>6,  'name'=>'Arancini di Riso',         'price'=>9.50,  'cat'=>'Starters', 'desc'=>'Fried rice balls with mozzarella and tomato ragù',      'allergens'=>['gluten','wheat','milk'],       'dietary'=>['vegetarian']],
            /* Pasta & Risotto */
            ['id'=>7,  'name'=>'Spaghetti Bolognese',      'price'=>14.50, 'cat'=>'Pasta',    'desc'=>'Classic slow-cooked meat sauce with fresh spaghetti',  'allergens'=>['gluten','wheat','milk'],       'dietary'=>[]],
            ['id'=>8,  'name'=>'Pasta Carbonara',          'price'=>13.50, 'cat'=>'Pasta',    'desc'=>'Egg, guanciale, pecorino romano and black pepper',      'allergens'=>['gluten','wheat','egg'],        'dietary'=>[]],
            ['id'=>9,  'name'=>'Penne Arrabbiata',         'price'=>11.00, 'cat'=>'Pasta',    'desc'=>'Penne with spicy tomato and garlic sauce',              'allergens'=>['gluten','wheat'],              'dietary'=>['vegan']],
            ['id'=>10, 'name'=>'Mushroom Risotto',         'price'=>15.00, 'cat'=>'Pasta',    'desc'=>'Creamy arborio rice with porcini mushrooms and parmesan','allergens'=>['milk'],                      'dietary'=>['vegetarian']],
            /* Mains */
            ['id'=>11, 'name'=>'Grilled Salmon',           'price'=>22.00, 'cat'=>'Mains',    'desc'=>'Atlantic salmon fillet with seasonal vegetables',       'allergens'=>['fish'],                        'dietary'=>[]],
            ['id'=>12, 'name'=>'Beef Tenderloin',          'price'=>28.00, 'cat'=>'Mains',    'desc'=>'200g prime beef with roasted potatoes and green beans', 'allergens'=>[],                              'dietary'=>[]],
            ['id'=>13, 'name'=>'Osso Buco',                'price'=>24.00, 'cat'=>'Mains',    'desc'=>'Braised veal shank with gremolata and creamy polenta',  'allergens'=>['milk'],                        'dietary'=>[]],
            ['id'=>14, 'name'=>'Pollo alla Cacciatora',    'price'=>17.50, 'cat'=>'Mains',    'desc'=>'Slow-braised chicken with olives, capers and tomatoes', 'allergens'=>[],                              'dietary'=>[]],
            ['id'=>15, 'name'=>'Branzino al Forno',        'price'=>22.00, 'cat'=>'Mains',    'desc'=>'Oven-baked sea bass with lemon and herbs',              'allergens'=>['fish'],                        'dietary'=>[]],
            ['id'=>16, 'name'=>'Vegan Buddha Bowl',        'price'=>11.50, 'cat'=>'Mains',    'desc'=>'Quinoa, roasted vegetables, avocado and tahini dressing','allergens'=>[],                            'dietary'=>['vegan','vegetarian']],
            /* Desserts */
            ['id'=>17, 'name'=>'Tiramisu',                 'price'=>7.50,  'cat'=>'Desserts', 'desc'=>'Mascarpone cream with espresso-soaked ladyfingers',    'allergens'=>['gluten','wheat','milk','nuts'], 'dietary'=>['vegetarian']],
            ['id'=>18, 'name'=>'Panna Cotta',              'price'=>6.50,  'cat'=>'Desserts', 'desc'=>'Vanilla cream with fresh berry coulis',                'allergens'=>['milk'],                        'dietary'=>['vegetarian']],
            ['id'=>19, 'name'=>'Gelato al Limone',         'price'=>5.50,  'cat'=>'Desserts', 'desc'=>'Homemade lemon sorbet — dairy-free',                   'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>20, 'name'=>'Caffè Affogato',           'price'=>5.00,  'cat'=>'Desserts', 'desc'=>'Vanilla ice cream drowned in a shot of espresso',      'allergens'=>['milk'],                        'dietary'=>['vegetarian']],
            /* Drinks */
            ['id'=>21, 'name'=>'Acqua Minerale',           'price'=>3.00,  'cat'=>'Drinks',   'desc'=>'Still or sparkling, 75cl',                             'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>22, 'name'=>'Vino Rosso della Casa',    'price'=>6.50,  'cat'=>'Drinks',   'desc'=>'House red wine, glass',                                'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>23, 'name'=>'Vino Bianco della Casa',   'price'=>6.50,  'cat'=>'Drinks',   'desc'=>'House white wine, glass',                              'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>24, 'name'=>'Succo di Frutta',          'price'=>4.00,  'cat'=>'Drinks',   'desc'=>'Fresh fruit juice — orange, apple or pineapple',       'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            /* Sides */
            ['id'=>25, 'name'=>'Verdure Grigliate',        'price'=>6.00,  'cat'=>'Sides',    'desc'=>'Seasonal grilled vegetables with olive oil',           'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>26, 'name'=>'Patate al Forno',          'price'=>5.50,  'cat'=>'Sides',    'desc'=>'Crispy oven-roasted potatoes with rosemary',           'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>27, 'name'=>'Pane e Coperto',           'price'=>3.50,  'cat'=>'Sides',    'desc'=>'Freshly baked bread with olive oil and butter',        'allergens'=>['gluten','wheat','milk'],       'dietary'=>['vegetarian']],
        ];

        $tables = ['A1','A2','A3','A4','A5','B1','B2','B3','B4','B7','C1','C2','C3','A12'];
        $categories = ['Starters','Pasta','Mains','Desserts','Drinks','Sides'];
        @endphp

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 pb-32 flex flex-col gap-5">

            <!-- ── Page header ──────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-black text-primary">New Order</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Select dishes for the guest &mdash; Molveno Lake Resort Restaurant</p>
                </div>

                <!-- Table selector + logged-in server -->
                <div class="flex items-center gap-3">
                    <!-- Server pill -->
                    <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 shadow-sm">
                        <div class="w-6 h-6 rounded-full bg-molveno-blue-500 flex items-center justify-center text-white shrink-0">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-gray-700">John Doe</span>
                    </div>

                    <!-- Table picker -->
                    <div class="relative">
                        <select id="sel-table"
                                class="appearance-none text-sm font-semibold border border-gray-200 rounded-lg pl-9 pr-8 py-2 bg-white text-gray-700 shadow-sm
                                       focus:outline-none focus:ring-2 focus:ring-molveno-blue-300 cursor-pointer">
                            <option value="">Select table</option>
                            @foreach($tables as $t)
                                <option value="{{ $t }}">Table {{ $t }}</option>
                            @endforeach
                        </select>
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
            </div>

            <!-- ── Filters ───────────────────────────────────── -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col gap-3">

                <!-- Search -->
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input id="search-input" type="search" placeholder="Search dishes…"
                           class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-white
                                  focus:outline-none focus:ring-2 focus:ring-molveno-blue-300 focus:border-transparent"
                           oninput="applyFilters()">
                </div>

                <!-- Category pills -->
                <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-0.5">
                    <button class="filter-btn filter-active" data-cat="all" onclick="setCategory(this)">All</button>
                    @foreach($categories as $cat)
                        <button class="filter-btn" data-cat="{{ $cat }}" onclick="setCategory(this)">{{ $cat }}</button>
                    @endforeach
                </div>

                <!-- Dietary + free-from -->
                <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                    <span class="text-xs font-semibold text-gray-500 shrink-0">Dietary:</span>

                    <button class="filter-btn" data-dietary="vegetarian" onclick="toggleDietary(this)">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 rounded-full bg-green-500 diet-icon-veg inline-flex items-center justify-center">
                                <svg viewBox="0 0 16 16" width="8" height="8"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg>
                            </span>
                            Vegetarian
                        </span>
                    </button>

                    <button class="filter-btn" data-dietary="vegan" onclick="toggleDietary(this)">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 rounded-full bg-green-700 diet-icon-vegan inline-flex items-center justify-center">
                                <svg viewBox="0 0 16 16" width="8" height="8"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg>
                            </span>
                            Vegan
                        </span>
                    </button>

                    <span class="text-gray-300 hidden sm:inline">|</span>
                    <span class="text-xs font-semibold text-gray-500 shrink-0">Free from:</span>

                    @foreach($allergenConfig as $key => $cfg)
                        <button class="filter-btn" data-freefrom="{{ $key }}" onclick="toggleFreefrom(this)">
                            <span class="inline-flex items-center gap-1.5">
                                <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" size="sm" />
                                {{ $cfg['label'] }}-free
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- ── Dish grid ──────────────────────────────────── -->
            <div id="dish-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($dishes as $dish)
                    <x-ordermanagement.dish-card :dish="$dish" :allergenConfig="$allergenConfig" />
                @endforeach
            </div>

            <!-- No results -->
            <div id="no-results" class="hidden text-center py-14">
                <svg class="mx-auto mb-3 text-gray-300" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <p class="text-gray-500 font-semibold">No dishes match your filters.</p>
                <button onclick="resetFilters()" class="mt-3 text-sm font-semibold text-molveno-blue-500 hover:underline">Clear filters</button>
            </div>

        </div>

        <x-ordermanagement.order-bar />
        <x-ordermanagement.review-screen />
        <x-ordermanagement.add-dish-modal />

        <!-- Toast -->
        <div id="toast"
             class="fixed top-5 left-1/2 -translate-x-1/2 z-50
                    bg-green-600 text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-xl
                    flex items-center gap-2 whitespace-nowrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
            Order sent to kitchen!
        </div>

        <script>
        /* ═══════════════════════════════════════════════════════
           Menu data (mirrors PHP arrays above)
        ═══════════════════════════════════════════════════════ */
        const MENU = @json($dishes);
        const ALLERGEN = @json($allergenConfig);

        /* ═══════════════════════════════════════════════════════
           Order state: { [id]: { dish, qty, note } }
        ═══════════════════════════════════════════════════════ */
        let order = {};

        /* ═══════════════════════════════════════════════════════
           Filter state
        ═══════════════════════════════════════════════════════ */
        let activeCat     = 'all';
        let activeDietary = new Set();
        let activeFreefrom = new Set();

        function setCategory(btn) {
            activeCat = btn.dataset.cat;
            document.querySelectorAll('[data-cat]').forEach(b => b.classList.toggle('filter-active', b === btn));
            applyFilters();
        }

        function toggleDietary(btn) {
            const val = btn.dataset.dietary;
            activeDietary.has(val) ? activeDietary.delete(val) : activeDietary.add(val);
            btn.classList.toggle('filter-active', activeDietary.has(val));
            applyFilters();
        }

        function toggleFreefrom(btn) {
            const val = btn.dataset.freefrom;
            activeFreefrom.has(val) ? activeFreefrom.delete(val) : activeFreefrom.add(val);
            btn.classList.toggle('filter-active', activeFreefrom.has(val));
            applyFilters();
        }

        function applyFilters() {
            const q = document.getElementById('search-input').value.toLowerCase().trim();
            let visible = 0;

            document.querySelectorAll('.dish-card').forEach(card => {
                const catOk      = activeCat === 'all' || card.dataset.cat === activeCat;
                const nameOk     = !q || card.dataset.name.includes(q);
                const dietaryOk  = activeDietary.size === 0 || [...activeDietary].every(d => card.dataset.dietary.includes(d));
                const freefromOk = activeFreefrom.size === 0 || [...activeFreefrom].every(a => !card.dataset.allergens.includes(a));

                const show = catOk && nameOk && dietaryOk && freefromOk;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            document.getElementById('no-results').classList.toggle('hidden', visible > 0);
        }

        function resetFilters() {
            activeCat = 'all';
            activeDietary.clear();
            activeFreefrom.clear();
            document.getElementById('search-input').value = '';
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('filter-active'));
            document.querySelector('[data-cat="all"]').classList.add('filter-active');
            applyFilters();
        }

        /* ═══════════════════════════════════════════════════════
           Add-dish modal
        ═══════════════════════════════════════════════════════ */
        let addModalDishId = null;
        let addModalQty    = 1;

        function addDish(id) {
            const dish = MENU.find(d => d.id === id);
            if (!dish) return;

            addModalDishId = id;
            addModalQty = order[id] ? 1 : 1;

            document.getElementById('add-modal-name').textContent  = dish.name;
            document.getElementById('add-modal-price').textContent = '€ ' + dish.price.toFixed(2);
            document.getElementById('add-modal-qty').textContent   = addModalQty;
            document.getElementById('add-modal-note').value        = '';

            const badgeRow = document.getElementById('add-modal-badges');
            badgeRow.innerHTML = '';
            dish.allergens.forEach(a => {
                if (!ALLERGEN[a]) return;
                const d = document.createElement('div');
                d.title = ALLERGEN[a].label;
                d.className = 'w-5 h-5 rounded-full flex items-center justify-center shrink-0 shadow-sm';
                d.style.backgroundColor = ALLERGEN[a].bg;
                d.innerHTML = `<svg viewBox="0 0 16 16" width="10" height="10">${ALLERGEN[a].icon}</svg>`;
                badgeRow.appendChild(d);
            });
            if (dish.dietary.includes('vegetarian')) {
                badgeRow.insertAdjacentHTML('beforeend', `<div title="Vegetarian" class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center shrink-0"><svg viewBox="0 0 16 16" width="10" height="10"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg></div>`);
            }
            if (dish.dietary.includes('vegan')) {
                badgeRow.insertAdjacentHTML('beforeend', `<div title="Vegan" class="w-5 h-5 rounded-full bg-green-700 flex items-center justify-center shrink-0"><svg viewBox="0 0 16 16" width="10" height="10"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg></div>`);
            }
            if (badgeRow.children.length === 0) {
                badgeRow.innerHTML = '<span class="text-xs text-gray-400">No allergens</span>';
            }

            document.getElementById('add-overlay').classList.add('open');
            document.body.style.overflow = 'hidden';
            setTimeout(() => document.getElementById('add-modal-note').focus(), 220);
        }

        function addModalChangeQty(delta) {
            addModalQty = Math.max(1, addModalQty + delta);
            document.getElementById('add-modal-qty').textContent = addModalQty;
        }

        function confirmAddDish() {
            const id   = addModalDishId;
            const dish = MENU.find(d => d.id === id);
            if (!dish) return;
            const note = document.getElementById('add-modal-note').value.trim();

            if (order[id]) {
                order[id].qty  += addModalQty;
                if (note) order[id].note = note;
            } else {
                order[id] = { dish, qty: addModalQty, note };
            }

            updateBadge(id);
            updateOrderBar();
            closeAddModal();
        }

        function closeAddModal(e) {
            if (e instanceof MouseEvent && e.target !== document.getElementById('add-overlay')) return;
            document.getElementById('add-overlay').classList.remove('open');
            document.body.style.overflow = '';
            addModalDishId = null;
        }

        /* ═══════════════════════════════════════════════════════
           Order management
        ═══════════════════════════════════════════════════════ */

        function removeDish(id) {
            delete order[id];
            updateBadge(id);
            updateOrderBar();
            renderReviewCard();
        }

        function changeQty(id, delta) {
            if (!order[id]) return;
            const newQty = order[id].qty + delta;
            if (newQty < 1) { removeDish(id); return; }
            order[id].qty = newQty;
            updateBadge(id);
            updateOrderBar();
            renderReviewCard();
        }

        function updateNote(id, val) {
            if (order[id]) order[id].note = val;
        }

        /* ── Badge on dish card ── */
        function updateBadge(id) {
            const badge = document.getElementById('badge-' + id);
            if (!badge) return;
            const item = order[id];
            if (item && item.qty > 0) {
                badge.textContent = item.qty;
                badge.classList.add('visible');
            } else {
                badge.classList.remove('visible');
            }
        }

        /* ── Sticky order bar ── */
        function updateOrderBar() {
            const items  = Object.values(order);
            const count  = items.reduce((s, i) => s + i.qty, 0);
            const total  = items.reduce((s, i) => s + i.dish.price * i.qty, 0);
            const table  = document.getElementById('sel-table').value;
            const bar    = document.getElementById('order-bar');

            document.getElementById('bar-count').textContent  = count;
            document.getElementById('bar-item-label').textContent = count === 1 ? 'item' : 'items';
            document.getElementById('bar-total').textContent  = '€ ' + total.toFixed(2);
            document.getElementById('bar-table').textContent  = table ? 'Table ' + table : 'No table selected';

            if (count > 0) {
                bar.classList.remove('hidden-bar');
            } else {
                bar.classList.add('hidden-bar');
            }
        }

        document.getElementById('sel-table').addEventListener('change', updateOrderBar);

        /* ═══════════════════════════════════════════════════════
           Review screen
        ═══════════════════════════════════════════════════════ */
        function openReview() {
            if (Object.keys(order).length === 0) return;
            renderReviewCard();
            document.getElementById('review-screen').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeReview() {
            document.getElementById('review-screen').classList.remove('open');
            document.body.style.overflow = '';
        }

        function renderReviewCard() {
            const items  = Object.values(order);
            const table  = document.getElementById('sel-table').value || '—';
            const now    = new Date();
            const time   = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
            const orderId = 'ORD-' + String(Math.floor(Math.random() * 900) + 100).padStart(3,'0');

            const cntPending = items.reduce((s, i) => s + i.qty, 0);
            const total      = items.reduce((s, i) => s + i.dish.price * i.qty, 0);

            document.getElementById('review-nav-table').textContent  = table !== '—' ? 'Table ' + table : 'No table';
            document.getElementById('review-meta-time').textContent  = time;
            document.getElementById('review-total').textContent      = '€ ' + total.toFixed(2);

            document.getElementById('review-ticket-header').style.backgroundColor = '#0084c4';
            document.getElementById('review-ticket-header').innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1 text-xs font-bold bg-black/20 px-2 py-0.5 rounded">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/>
                                    <path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                                </svg>
                                Table ${esc(table)}
                            </span>
                            <span class="text-sm font-bold tracking-wide">${orderId}</span>
                        </div>
                        <p class="text-xs opacity-70 mt-0.5">John Doe</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs font-semibold">${time}</p>
                        <p class="text-xs opacity-70 mt-0.5">${cntPending} preparing</p>
                    </div>
                </div>`;

            document.getElementById('review-dish-list').innerHTML = items.map(({ dish, qty, note }) => {
                const allergenHtml = dish.allergens.length
                    ? `<div class="flex items-center gap-1 flex-wrap mt-1">
                        ${dish.allergens.map(a => ALLERGEN[a]
                            ? `<div title="${esc(ALLERGEN[a].label)}"
                                    class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 shadow-sm"
                                    style="background-color:${ALLERGEN[a].bg}">
                                    <svg viewBox="0 0 16 16" width="10" height="10">${ALLERGEN[a].icon}</svg>
                               </div>`
                            : '').join('')}
                       </div>`
                    : '';

                const dietaryHtml = dish.dietary.length
                    ? `<div class="flex items-center gap-1 flex-wrap mt-1">
                        ${dish.dietary.includes('vegetarian')
                            ? `<div title="Vegetarian" class="w-4 h-4 rounded-full bg-green-500 flex items-center justify-center shrink-0"><svg viewBox="0 0 16 16" width="8" height="8"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg></div>` : ''}
                        ${dish.dietary.includes('vegan')
                            ? `<div title="Vegan" class="w-4 h-4 rounded-full bg-green-700 flex items-center justify-center shrink-0"><svg viewBox="0 0 16 16" width="8" height="8"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg></div>` : ''}
                       </div>`
                    : '';

                const noteHtml = note.trim()
                    ? `<p class="text-xs text-gray-400 italic mt-1 leading-snug">"${esc(note)}"</p>`
                    : '';

                return `
                <div class="px-4 py-3 flex flex-col gap-1.5">
                    <div class="flex items-start gap-2">
                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 bg-gray-300 flex-shrink-0"></span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline justify-between gap-1">
                                <span class="text-sm font-semibold text-gray-800 leading-snug">${esc(dish.name)}</span>
                                <span class="text-xs text-gray-400 font-medium shrink-0">&times;${qty}</span>
                            </div>
                            ${allergenHtml}${dietaryHtml}${noteHtml}
                        </div>
                    </div>
                    <div class="pl-4">
                        <textarea class="note-area" rows="1"
                                  placeholder="Add notes for this dish…"
                                  oninput="updateNote(${dish.id}, this.value); updateNotePreview(${dish.id}, this.value)">${esc(note)}</textarea>
                    </div>
                    <div class="pl-4 flex items-center gap-2">
                        <button class="qty-btn" onclick="changeQty(${dish.id}, -1)">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
                        </button>
                        <span class="text-sm font-bold text-gray-700 w-5 text-center" id="review-qty-${dish.id}">${qty}</span>
                        <button class="qty-btn" onclick="changeQty(${dish.id}, 1)">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        </button>
                        <span class="text-xs text-gray-400 ms-1">€ ${(dish.price * qty).toFixed(2)}</span>
                        <button onclick="removeDish(${dish.id})" class="ms-auto text-gray-300 hover:text-red-500 transition-colors">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>`;
            }).join('');

            const dishCount = items.length;
            document.getElementById('review-card-footer').innerHTML = `
                <span class="text-xs text-gray-400">${dishCount} ${dishCount === 1 ? 'dish' : 'dishes'} &middot; 0 served</span>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-400">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 1.5"/>
                    </svg>
                    Preparing &middot; <strong class="text-gray-700 ms-0.5">&euro; ${total.toFixed(2)}</strong>
                </span>`;
        }

        function updateNotePreview(id, val) {
            // notes already stored via updateNote(); re-render not needed since
            // the textarea itself is the source of truth in the review card.
        }

        /* ═══════════════════════════════════════════════════════
           Send order
        ═══════════════════════════════════════════════════════ */
        function sendOrder() {
            const table = document.getElementById('sel-table').value;
            if (!table) {
                alert('Please select a table before sending the order.');
                return;
            }
            closeReview();
            Object.keys(order).forEach(id => updateBadge(id));
            order = {};
            updateOrderBar();
            const toast = document.getElementById('toast');
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3200);
        }

        /* ═══════════════════════════════════════════════════════
           Helpers
        ═══════════════════════════════════════════════════════ */
        function esc(str) {
            return String(str ?? '')
                .replace(/&/g,'&amp;')
                .replace(/</g,'&lt;')
                .replace(/>/g,'&gt;')
                .replace(/"/g,'&quot;');
        }
        </script>
    </body>
</html>
