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
        <x-ordermanagement.styles />
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.navigation')

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 pb-32 flex flex-col gap-5">

            <!-- ── Page header ──────────────────────────────── -->
            <x-ui.page-header title="New Order" subtitle="Select dishes for the guest — Molveno Lake Resort Restaurant">
                <x-slot:actions>
                    <x-ordermanagement.table-picker :tables="$tables" />
                </x-slot:actions>
            </x-ui.page-header>

            <!-- ── Filters ───────────────────────────────────── -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col gap-3">

                <!-- Search -->
                <x-ui.search-input id="search-input" placeholder="Search dishes…" oninput="applyFilters()" />

                <!-- Category pills -->
                <x-ui.tab-group class="overflow-x-auto scrollbar-hide pb-0.5">
                    <x-ui.tab :active="true" value="all" data-cat="all" onclick="setCategory(this)">All</x-ui.tab>
                    @foreach($categories as $cat)
                        <x-ui.tab :active="false" value="{{ $cat }}" data-cat="{{ $cat }}" onclick="setCategory(this)">{{ $cat }}</x-ui.tab>
                    @endforeach
                </x-ui.tab-group>

                <!-- Dietary + free-from -->
                <x-ordermanagement.filter-bar :allergenConfig="$allergenConfig" />
            </div>

            <!-- ── Dish grid ──────────────────────────────────── -->
            <x-ordermanagement.dish-grid>
                @foreach($dishes as $dish)
                    <x-ordermanagement.dish-card :dish="$dish" :allergenConfig="$allergenConfig" />
                @endforeach
            </x-ordermanagement.dish-grid>

            <!-- No results -->
            <div id="no-results" class="hidden">
                <x-ui.empty-state title="No dishes match your filters.">
                    <x-slot:icon>
                        <svg class="w-10 h-10 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </x-slot:icon>
                    <x-slot:action>
                        <x-ui.button variant="ghost" onclick="resetFilters()" size="sm">Clear filters</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </div>

        </div>

        <x-ordermanagement.order-bar />
        <x-ordermanagement.review-screen />
        <x-ordermanagement.add-dish-modal />

        <x-ui.toast />

        <x-ordermanagement.scripts :dishes="$dishes" :allergenConfig="$allergenConfig" />
    @livewireScripts
    </body>
</html>
