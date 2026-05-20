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

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-8 flex flex-col gap-5">

            <!-- ── Page header ───────────────────────────────────── -->
            <x-ui.page-header title="Dish Menu" subtitle="Molveno Lake Resort — Restaurant" help-page="dishes" help-title="How to use the Dish Menu">
                <x-slot:actions>
                    <x-ui.button onclick="openCreateSheet()" title="Create a new dish for the menu">
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
