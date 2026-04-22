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

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5">

            <!-- ── Page header ───────────────────────────────── -->
            <x-ui.page-header title="Kitchen Orders" subtitle="Live order queue — Molveno Lake Resort">
                <x-slot:actions>
                    <x-orders.status-summary :totalPending="$totalPending" :totalReady="$totalReady" :countCompleted="$countCompleted" />
                </x-slot:actions>
            </x-ui.page-header>

            <!-- ── Filter tabs ──────────────────────────────── -->
            <x-orders.filter-tabs :orderCount="count($orders)" :countActive="$countActive" :countCompleted="$countCompleted" />

            <!-- ── KDS order grid ───────────────────────────── -->
            <x-orders.order-grid>
                @foreach($orders as $order)
                    <x-orders.order-card :order="$order" :allergenConfig="$allergenConfig" />
                @endforeach
            </x-orders.order-grid>

            <!-- Empty state -->
            <div id="no-orders" class="hidden">
                <x-ui.empty-state title="No orders match this filter.">
                    <x-slot:icon>
                        <svg class="text-gray-300 mb-3" width="44" height="44" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                            <rect x="9" y="3" width="6" height="4" rx="1"/>
                        </svg>
                    </x-slot:icon>
                </x-ui.empty-state>
            </div>

        </div>

        <x-orders.scripts />
    @livewireScripts
    </body>
</html>
