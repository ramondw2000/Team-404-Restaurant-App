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
        <x-dishes.styles />
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.navigation')

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
            <x-ui.page-header title="Dish Menu" subtitle="Molveno Lake Resort — Restaurant">
                <x-slot:actions>
                    <x-ui.button onclick="openCreateSheet()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Dish
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.page-header>

            <!-- Legend -->
            <x-dishes.allergen-legend :allergenConfig="$allergenConfig" />

            <!-- ── Filters ───────────────────────────────────────── -->
            <div class="flex flex-col gap-3">

                <!-- Search -->
                <x-ui.search-input id="search-input" placeholder="Search dishes…" />

                <!-- Category pills — scrollable on mobile -->
                <x-ui.tab-group class="overflow-x-auto pb-0.5 scrollbar-hide">
                    <x-ui.tab :active="true" value="all" data-filter="category" data-value="all" onclick="setCategory(this)">All</x-ui.tab>
                    @foreach(['Starters','Mains','Desserts','Drinks','Sides'] as $cat)
                        <x-ui.tab :active="false" value="{{ $cat }}" data-filter="category" data-value="{{ $cat }}" onclick="setCategory(this)">{{ $cat }}</x-ui.tab>
                    @endforeach
                </x-ui.tab-group>

                <!-- Dietary + free-from -->
                <x-dishes.filter-bar :allergenConfig="$allergenConfig" />
            </div>

            <!-- ── Dish grid ─────────────────────────────────────── -->
            <x-dishes.dish-grid>
                @foreach($dishes as $dish)
                    <x-dishes.dish-card :dish="$dish" :allergenConfig="$allergenConfig" />
                @endforeach
            </x-dishes.dish-grid>

            <!-- No results -->
            <div id="no-results" class="hidden">
                <x-ui.empty-state title="No dishes match your filters." description="Try adjusting your search or filters.">
                    <x-slot:icon>
                        <svg class="w-10 h-10 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                    </x-slot:icon>
                    <x-slot:action>
                        <x-ui.button variant="ghost" onclick="resetFilters()" size="sm">
                            Clear all filters
                        </x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </div>

        </div>

        <x-dishes.dish-sheet />

        <x-dishes.scripts />
    @livewireScripts
    </body>
</html>
