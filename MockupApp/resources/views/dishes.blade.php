<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Dishes - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            /* ── Sheet ───────────────────────────────────────────── */
            .sheet-overlay {
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s ease;
            }
            .sheet-overlay.open { opacity: 1; pointer-events: auto; }

            .sheet-panel {
                transform: translateX(100%);
                transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
                height: 100vh;
                height: 100dvh;
            }
            .sheet-panel.open { transform: translateX(0); }

            .sheet-input {
                width: 100%;
                border: 1px solid #e5e7eb;
                border-radius: 0.5rem;
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
                color: #111827;
                background: #fff;
                outline: none;
                transition: border-color 0.15s, box-shadow 0.15s;
                font-family: inherit;
            }
            .sheet-input:focus {
                border-color: #309bcf;
                box-shadow: 0 0 0 3px rgba(48, 155, 207, 0.2);
            }
            .sheet-input::placeholder { color: #9ca3af; }

            .allergen-checkbox { display: none; }
            .allergen-label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.4rem 0.75rem;
                border: 1px solid #e5e7eb;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                cursor: pointer;
                user-select: none;
                transition: border-color 0.15s, background 0.15s, color 0.15s;
                color: #374151;
                background: #f9fafb;
            }
            .allergen-checkbox:checked + .allergen-label {
                border-color: #309bcf;
                background: #eaf4fa;
                color: #005693;
            }

            .upload-zone {
                border: 2px dashed #d1d5db;
                border-radius: 0.75rem;
                padding: 1.75rem 1rem;
                text-align: center;
                cursor: pointer;
                transition: border-color 0.15s, background 0.15s;
            }
            .upload-zone:hover { border-color: #309bcf; background: #f0f9ff; }

            /* ── Filter pills ────────────────────────────────────── */
            .filter-btn {
                padding: 0.35rem 1rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                border: 1px solid #e5e7eb;
                background: #fff;
                color: #374151;
                cursor: pointer;
                transition: border-color 0.15s, background 0.15s, color 0.15s;
                white-space: nowrap;
                font-family: inherit;
            }
            .filter-btn:hover { border-color: #309bcf; color: #005693; }
            .filter-btn.filter-active {
                background: #005693;
                border-color: #005693;
                color: #fff;
            }

            /* ── Dish grid ───────────────────────────────────────── */
            .dish-grid { grid-template-columns: repeat(5, 200px); }
            .dish-card  { width: 200px; height: 240px; box-sizing: border-box; }

            @media (max-width: 1279px) {
                .dish-grid { grid-template-columns: repeat(4, 1fr); }
                .dish-card  { width: 100%; height: auto; aspect-ratio: 5 / 6; box-sizing: border-box; }
            }
            @media (max-width: 767px) {
                .dish-grid { grid-template-columns: repeat(3, 1fr); }
            }
            @media (max-width: 639px) {
                .dish-grid { grid-template-columns: repeat(2, 1fr); }
            }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.guest-navigation')

        @php
        $dishes = [
            /* ── Starters ─────────────────────────────────────── */
            ['name'=>'Bruschetta al Pomodoro',      'price'=>7.00,  'category'=>'Starters', 'allergens'=>['gluten','wheat'],              'dietary'=>['vegan'],                  'color'=>'#c07830'],
            ['name'=>'Caprese Salad',                'price'=>9.00,  'category'=>'Starters', 'allergens'=>['milk'],                        'dietary'=>['vegetarian'],             'color'=>'#5a9e6e'],
            ['name'=>'Caesar Salad',                 'price'=>10.50, 'category'=>'Starters', 'allergens'=>['gluten','milk'],               'dietary'=>[],                         'color'=>'#6b9e7e'],
            ['name'=>'Minestrone Soup',              'price'=>7.00,  'category'=>'Starters', 'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#7a9e6e'],
            ['name'=>'Arancini di Riso',             'price'=>9.50,  'category'=>'Starters', 'allergens'=>['gluten','wheat','milk'],       'dietary'=>['vegetarian'],             'color'=>'#d4a050'],
            ['name'=>'Antipasto Misto',              'price'=>11.00, 'category'=>'Starters', 'allergens'=>['milk'],                        'dietary'=>[],                         'color'=>'#c06050'],
            ['name'=>'Vitello Tonnato',              'price'=>12.50, 'category'=>'Starters', 'allergens'=>[],                              'dietary'=>[],                         'color'=>'#d4c090'],
            ['name'=>'Insalata di Mare',             'price'=>10.00, 'category'=>'Starters', 'allergens'=>[],                              'dietary'=>[],                         'color'=>'#3a8eb0'],
            ['name'=>'Panzanella',                   'price'=>8.50,  'category'=>'Starters', 'allergens'=>['gluten','wheat'],              'dietary'=>['vegan','vegetarian'],     'color'=>'#e07030'],
            ['name'=>'Focaccia al Rosmarino',        'price'=>5.50,  'category'=>'Starters', 'allergens'=>['gluten','wheat'],              'dietary'=>['vegan'],                  'color'=>'#a07030'],
            /* ── Mains ────────────────────────────────────────── */
            ['name'=>'Spaghetti Bolognese',          'price'=>14.50, 'category'=>'Mains',    'allergens'=>['gluten','wheat','milk'],       'dietary'=>[],                         'color'=>'#c0603a'],
            ['name'=>'Margherita Pizza',             'price'=>12.00, 'category'=>'Mains',    'allergens'=>['gluten','wheat','milk'],       'dietary'=>['vegetarian'],             'color'=>'#d4a836'],
            ['name'=>'Grilled Salmon',               'price'=>18.00, 'category'=>'Mains',    'allergens'=>[],                              'dietary'=>[],                         'color'=>'#3a6ec0'],
            ['name'=>'Mushroom Risotto',             'price'=>13.00, 'category'=>'Mains',    'allergens'=>['milk'],                        'dietary'=>['vegetarian'],             'color'=>'#7a5c3a'],
            ['name'=>'Penne Arrabbiata',             'price'=>11.00, 'category'=>'Mains',    'allergens'=>['gluten','wheat'],              'dietary'=>['vegan'],                  'color'=>'#c05050'],
            ['name'=>'Beef Tenderloin',              'price'=>26.00, 'category'=>'Mains',    'allergens'=>[],                              'dietary'=>[],                         'color'=>'#7a3a2a'],
            ['name'=>'Pasta Carbonara',              'price'=>14.00, 'category'=>'Mains',    'allergens'=>['gluten','wheat','milk'],       'dietary'=>[],                         'color'=>'#b08a40'],
            ['name'=>'Vegan Buddha Bowl',            'price'=>11.50, 'category'=>'Mains',    'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#3a8e5a'],
            ['name'=>'Risotto ai Frutti di Mare',   'price'=>19.50, 'category'=>'Mains',    'allergens'=>['milk'],                        'dietary'=>[],                         'color'=>'#005693'],
            ['name'=>'Tagliatelle al Ragù',          'price'=>13.50, 'category'=>'Mains',    'allergens'=>['gluten','wheat','milk'],       'dietary'=>[],                         'color'=>'#8a3020'],
            ['name'=>'Lasagne al Forno',             'price'=>15.00, 'category'=>'Mains',    'allergens'=>['gluten','wheat','milk'],       'dietary'=>['vegetarian'],             'color'=>'#c04030'],
            ['name'=>'Osso Buco',                    'price'=>23.00, 'category'=>'Mains',    'allergens'=>[],                              'dietary'=>[],                         'color'=>'#7a5020'],
            ['name'=>'Saltimbocca alla Romana',      'price'=>20.00, 'category'=>'Mains',    'allergens'=>['gluten','wheat'],              'dietary'=>[],                         'color'=>'#b06050'],
            ['name'=>'Branzino al Forno',            'price'=>22.00, 'category'=>'Mains',    'allergens'=>[],                              'dietary'=>[],                         'color'=>'#4a7e9e'],
            ['name'=>'Pollo alla Cacciatora',        'price'=>17.50, 'category'=>'Mains',    'allergens'=>[],                              'dietary'=>[],                         'color'=>'#c07840'],
            ['name'=>'Gnocchi al Gorgonzola',        'price'=>14.00, 'category'=>'Mains',    'allergens'=>['gluten','wheat','milk','nuts'],'dietary'=>['vegetarian'],             'color'=>'#6a5e9e'],
            ['name'=>'Ribollita',                    'price'=>12.00, 'category'=>'Mains',    'allergens'=>['gluten','wheat'],              'dietary'=>['vegan','vegetarian'],     'color'=>'#5a7e4a'],
            ['name'=>'Polenta e Funghi',             'price'=>13.00, 'category'=>'Mains',    'allergens'=>['milk'],                        'dietary'=>['vegetarian'],             'color'=>'#9a7040'],
            /* ── Desserts ─────────────────────────────────────── */
            ['name'=>'Tiramisu',                     'price'=>7.50,  'category'=>'Desserts', 'allergens'=>['gluten','wheat','milk','nuts'],'dietary'=>['vegetarian'],             'color'=>'#8e3a59'],
            ['name'=>'Panna Cotta',                  'price'=>6.50,  'category'=>'Desserts', 'allergens'=>['milk'],                        'dietary'=>['vegetarian'],             'color'=>'#309bcf'],
            ['name'=>'Mixed Nut Tart',               'price'=>8.00,  'category'=>'Desserts', 'allergens'=>['gluten','wheat','nuts','milk'],'dietary'=>['vegetarian'],             'color'=>'#6b4e2a'],
            ['name'=>'Cannoli Siciliani',             'price'=>7.00,  'category'=>'Desserts', 'allergens'=>['gluten','wheat','milk'],       'dietary'=>['vegetarian'],             'color'=>'#e0a040'],
            ['name'=>'Torta della Nonna',             'price'=>7.50,  'category'=>'Desserts', 'allergens'=>['gluten','wheat','milk','nuts'],'dietary'=>['vegetarian'],             'color'=>'#c09060'],
            ['name'=>'Gelato al Limone',              'price'=>5.50,  'category'=>'Desserts', 'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#e8c830'],
            ['name'=>'Semifreddo al Cioccolato',      'price'=>7.00,  'category'=>'Desserts', 'allergens'=>['milk'],                        'dietary'=>['vegetarian'],             'color'=>'#4a2010'],
            ['name'=>'Crostata di Ricotta',           'price'=>8.00,  'category'=>'Desserts', 'allergens'=>['gluten','wheat','milk'],       'dietary'=>['vegetarian'],             'color'=>'#d4a870'],
            ['name'=>'Caffè Affogato',                'price'=>5.00,  'category'=>'Desserts', 'allergens'=>['milk'],                        'dietary'=>['vegetarian'],             'color'=>'#3a2010'],
            /* ── Drinks ───────────────────────────────────────── */
            ['name'=>'Acqua Minerale',               'price'=>3.00,  'category'=>'Drinks',   'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#90c0e0'],
            ['name'=>'Vino Rosso della Casa',         'price'=>6.50,  'category'=>'Drinks',   'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#6a1020'],
            ['name'=>'Vino Bianco della Casa',        'price'=>6.50,  'category'=>'Drinks',   'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#c8b840'],
            ['name'=>'Limoncello',                   'price'=>5.00,  'category'=>'Drinks',   'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#c8d820'],
            ['name'=>'Spritz Aperol',                'price'=>6.00,  'category'=>'Drinks',   'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#e06010'],
            ['name'=>'Succo di Frutta',              'price'=>4.00,  'category'=>'Drinks',   'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#d04060'],
            /* ── Sides ────────────────────────────────────────── */
            ['name'=>'Pane e Coperto',               'price'=>3.50,  'category'=>'Sides',    'allergens'=>['gluten','wheat'],              'dietary'=>['vegan'],                  'color'=>'#c0a060'],
            ['name'=>'Verdure Grigliate',             'price'=>6.00,  'category'=>'Sides',    'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#5a9e4a'],
            ['name'=>'Patate al Forno',               'price'=>5.50,  'category'=>'Sides',    'allergens'=>[],                              'dietary'=>['vegan','vegetarian'],     'color'=>'#c09030'],
        ];

        $allergenConfig = [
            'gluten' => ['label'=>'Gluten', 'bg'=>'#D97706', 'icon'=>'<path fill="white" d="M8 1.5C6.5 3 5 5.5 5 7.5c0 1 .4 1.9 1 2.6V14h4V10.1c.6-.7 1-1.6 1-2.6 0-2-1.5-4.5-3-6z"/>'],
            'nuts'   => ['label'=>'Nuts',   'bg'=>'#92400E', 'icon'=>'<ellipse cx="8" cy="9.5" rx="5" ry="5.5" fill="white"/><path d="M5.5 5C5.5 3.3 6.6 2 8 2s2.5 1.3 2.5 3" stroke="#92400E" stroke-width="1" fill="none" stroke-linecap="round"/>'],
            'milk'   => ['label'=>'Milk',   'bg'=>'#0284C7', 'icon'=>'<path fill="white" d="M6 2h4l.5 2.5H5.5L6 2zM5 5h6l-1 9H6L5 5z"/>'],
            'wheat'  => ['label'=>'Wheat',  'bg'=>'#CA8A04', 'icon'=>'<line x1="8" y1="14" x2="8" y2="4" stroke="white" stroke-width="1.5"/><ellipse cx="5.5" cy="6" rx="2.5" ry="1.5" fill="white" transform="rotate(-20 5.5 6)"/><ellipse cx="10.5" cy="6" rx="2.5" ry="1.5" fill="white" transform="rotate(20 10.5 6)"/><ellipse cx="5" cy="9" rx="2.5" ry="1.5" fill="white" transform="rotate(-20 5 9)"/><ellipse cx="11" cy="9" rx="2.5" ry="1.5" fill="white" transform="rotate(20 11 9)"/><ellipse cx="8" cy="3" rx="1.5" ry="2" fill="white"/>'],
            'fish'   => ['label'=>'Fish',   'bg'=>'#0891B2', 'icon'=>'<path fill="white" d="M2 8c2-3 5-4 8-4s6 1 8 4c-2 3-5 4-8 4S4 11 2 8z"/><circle cx="13" cy="8" r="1.2" fill="#0891B2"/>'],
            'egg'    => ['label'=>'Egg',    'bg'=>'#7C3AED', 'icon'=>'<ellipse cx="8" cy="9" rx="5" ry="6" fill="white"/><ellipse cx="8" cy="10" rx="2.5" ry="3" fill="#7C3AED"/>'],
        ];
        @endphp

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-8 flex flex-col gap-5">

            <!-- ── Page header ───────────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-primary">Dish Menu</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Molveno Lake Resort &mdash; Restaurant</p>
                    </div>
                    <button onclick="openCreateSheet()"
                            class="shrink-0 flex items-center gap-2 bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm transition-colors duration-150">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Dish
                    </button>
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 sm:justify-end">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide w-full sm:w-auto">Legend:</span>
                    @foreach($allergenConfig as $key => $cfg)
                        <div class="flex items-center gap-1.5">
                            <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" />
                            <span class="text-xs font-medium text-gray-600">{{ $cfg['label'] }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center shrink-0">
                            <svg viewBox="0 0 16 16" width="11" height="11"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg>
                        </div>
                        <span class="text-xs font-medium text-gray-600">Vegetarian</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded-full bg-green-700 flex items-center justify-center shrink-0">
                            <svg viewBox="0 0 16 16" width="11" height="11"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg>
                        </div>
                        <span class="text-xs font-medium text-gray-600">Vegan</span>
                    </div>
                </div>
            </div>

            <!-- ── Filters ───────────────────────────────────────── -->
            <div class="flex flex-col gap-3">

                <!-- Search -->
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                         width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input id="search-input" type="text" placeholder="Search dishes…"
                           class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl bg-white shadow-sm text-sm
                                  focus:outline-none focus:ring-2 focus:ring-molveno-blue-300 focus:border-transparent"/>
                </div>

                <!-- Category pills — scrollable on mobile -->
                <div class="flex gap-2 overflow-x-auto pb-0.5 scrollbar-hide">
                    <button class="filter-btn filter-active" data-filter="category" data-value="all"
                            onclick="setCategory(this)">All</button>
                    @foreach(['Starters','Mains','Desserts','Drinks','Sides'] as $cat)
                        <button class="filter-btn" data-filter="category" data-value="{{ $cat }}"
                                onclick="setCategory(this)">{{ $cat }}</button>
                    @endforeach
                </div>

                <!-- Dietary + free-from -->
                <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                    <span class="text-xs font-semibold text-gray-500 shrink-0">Dietary:</span>
                    <button class="filter-btn" data-filter="dietary" data-value="vegetarian"
                            onclick="toggleMulti(this, 'dietary')">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-3.5 h-3.5 rounded-full bg-green-500 inline-flex items-center justify-center">
                                <svg viewBox="0 0 16 16" width="8" height="8"><path fill="black" d="M3 14c0-5 4-11 10-12C13 7 11 11 8 13l4-3c-1 3-5 5-9 4z"/></svg>
                            </span>
                            Vegetarian
                        </span>
                    </button>
                    <button class="filter-btn" data-filter="dietary" data-value="vegan"
                            onclick="toggleMulti(this, 'dietary')">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-3.5 h-3.5 rounded-full bg-green-700 inline-flex items-center justify-center">
                                <svg viewBox="0 0 16 16" width="8" height="8"><path stroke="black" stroke-width="1.5" fill="none" stroke-linecap="round" d="M8 14V8M8 8C8 5 5 2 2 2C2 5 5 8 8 8M8 8C8 5 11 2 14 2C14 5 11 8 8 8"/></svg>
                            </span>
                            Vegan
                        </span>
                    </button>

                    <span class="text-gray-300 hidden sm:inline">|</span>
                    <span class="text-xs font-semibold text-gray-500 shrink-0">Free from:</span>
                    @foreach($allergenConfig as $key => $cfg)
                        <button class="filter-btn" data-filter="freefrom" data-value="{{ $key }}"
                                onclick="toggleMulti(this, 'freefrom')">
                            <span class="inline-flex items-center gap-1">
                                <x-dishes.allergen-icon :bg="$cfg['bg']" :icon="$cfg['icon']" size="sm" />
                                {{ $cfg['label'] }}-free
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- ── Dish grid ─────────────────────────────────────── -->
            <div id="dish-grid" class="grid gap-4 justify-center dish-grid">
                @foreach($dishes as $dish)
                    <x-dishes.dish-card :dish="$dish" :allergenConfig="$allergenConfig" />
                @endforeach
            </div>

            <!-- No results -->
            <div id="no-results" class="hidden text-center py-16">
                <svg class="mx-auto mb-3 text-gray-300" width="48" height="48" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <p class="text-gray-500 font-semibold">No dishes match your filters.</p>
                <p class="text-gray-400 text-sm mt-1">Try adjusting your search or filters.</p>
                <button onclick="resetFilters()"
                        class="mt-4 text-sm font-semibold text-molveno-blue-500 hover:underline">
                    Clear all filters
                </button>
            </div>

        </div>

        <x-dishes.dish-sheet />

        <script>
            /* ── Sheet helpers ─────────────────────────────────── */
            function openSheet() {
                document.getElementById('sheet-overlay').classList.add('open');
                document.getElementById('sheet-panel').classList.add('open');
                document.body.style.overflow = 'hidden';
            }
            function closeSheet() {
                document.getElementById('sheet-overlay').classList.remove('open');
                document.getElementById('sheet-panel').classList.remove('open');
                document.body.style.overflow = '';
            }
            document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSheet(); });

            /* ── Create mode ───────────────────────────────────── */
            function openCreateSheet() {
                document.getElementById('sheet-title').textContent    = 'Add New Dish';
                document.getElementById('sheet-subtitle').textContent = 'Fill in the details below to add a dish to the menu.';
                document.getElementById('sheet-save-btn').textContent = 'Save Dish';
                document.getElementById('sheet-delete-btn').classList.add('hidden');
                document.getElementById('current-photo-preview').classList.add('hidden');
                document.getElementById('upload-zone-wrapper').classList.remove('hidden');
                // Clear form
                document.getElementById('dish-name').value   = '';
                document.getElementById('dish-desc').value   = '';
                document.getElementById('dish-price').value  = '';
                document.getElementById('dish-category').value = '';
                document.querySelectorAll('.allergen-checkbox').forEach(cb => cb.checked = false);
                openSheet();
            }

            /* ── Edit mode ─────────────────────────────────────── */
            function openEditSheet(card) {
                const name = card.querySelector('.font-bold').textContent.trim();

                document.getElementById('sheet-title').textContent    = 'Edit Dish';
                document.getElementById('sheet-subtitle').innerHTML   =
                    'Editing: <span class="font-semibold text-gray-600">' + name + '</span>';
                document.getElementById('sheet-save-btn').textContent = 'Update Dish';
                document.getElementById('sheet-delete-btn').classList.remove('hidden');

                // Photo preview
                document.getElementById('upload-zone-wrapper').classList.add('hidden');
                document.getElementById('current-photo-preview').classList.remove('hidden');
                document.getElementById('preview-bg').style.backgroundColor = card.dataset.color || '#309bcf';

                // Populate fields
                document.getElementById('dish-name').value    = name;
                document.getElementById('dish-desc').value    = '';   // no description data on cards
                document.getElementById('dish-price').value   = card.dataset.price  || '';
                document.getElementById('dish-category').value = card.dataset.category || '';

                const allergens = (card.dataset.allergens || '').split(',').filter(Boolean);
                document.getElementById('al-gluten').checked = allergens.includes('gluten');
                document.getElementById('al-nuts').checked   = allergens.includes('nuts');
                document.getElementById('al-milk').checked   = allergens.includes('milk');
                document.getElementById('al-wheat').checked  = allergens.includes('wheat');

                const dietary = (card.dataset.dietary || '').split(',').filter(Boolean);
                document.getElementById('diet-veg').checked   = dietary.includes('vegetarian');
                document.getElementById('diet-vegan').checked = dietary.includes('vegan');

                openSheet();
            }

            /* ── Filtering ─────────────────────────────────────── */
            const state = { category: 'all', dietary: [], freefrom: [] };

            function applyFilters() {
                const search  = document.getElementById('search-input').value.trim().toLowerCase();
                const cards   = document.querySelectorAll('#dish-grid .dish-card');
                let visible   = 0;

                cards.forEach(card => {
                    const name      = card.dataset.name      || '';
                    const category  = card.dataset.category  || '';
                    const allergens = card.dataset.allergens  ? card.dataset.allergens.split(',').filter(Boolean) : [];
                    const dietary   = card.dataset.dietary    ? card.dataset.dietary.split(',').filter(Boolean)   : [];

                    const passCategory = state.category === 'all' || category === state.category;
                    const passSearch   = !search || name.includes(search);
                    const passDietary  = state.dietary.every(d => dietary.includes(d));
                    const passFree     = state.freefrom.every(a => !allergens.includes(a));

                    const show = passCategory && passSearch && passDietary && passFree;
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                document.getElementById('no-results').classList.toggle('hidden', visible > 0);
            }

            function setCategory(btn) {
                state.category = btn.dataset.value;
                document.querySelectorAll('[data-filter="category"]')
                        .forEach(b => b.classList.toggle('filter-active', b === btn));
                applyFilters();
            }

            function toggleMulti(btn, key) {
                const val = btn.dataset.value;
                const idx = state[key].indexOf(val);
                if (idx === -1) state[key].push(val);
                else            state[key].splice(idx, 1);
                btn.classList.toggle('filter-active', state[key].includes(val));
                applyFilters();
            }

            function resetFilters() {
                state.category = 'all';
                state.dietary  = [];
                state.freefrom = [];
                document.getElementById('search-input').value = '';
                document.querySelectorAll('[data-filter="category"]')
                        .forEach(b => b.classList.toggle('filter-active', b.dataset.value === 'all'));
                document.querySelectorAll('[data-filter="dietary"], [data-filter="freefrom"]')
                        .forEach(b => b.classList.remove('filter-active'));
                applyFilters();
            }

            document.getElementById('search-input').addEventListener('input', applyFilters);
        </script>
    </body>
</html>
