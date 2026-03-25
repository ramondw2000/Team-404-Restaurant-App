<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Accounts - {{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <x-accounts.styles />
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.navigation')

        <x-ui.toast />

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5">

            <!-- ── Page header ───────────────────────────────── -->
            <x-ui.page-header title="Account Management" subtitle="Manage staff accounts — Molveno Lake Resort">
                <x-slot:actions>
                    <x-ui.button onclick="openSheet()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Account
                    </x-ui.button>
                </x-slot:actions>
            </x-ui.page-header>

            <!-- ── Role filter tabs ───────────────────────────── -->
            <x-accounts.role-tabs :counts="$counts" />

            <!-- ── User table ─────────────────────────────────── -->
            <x-accounts.user-table :users="$users" :roleConfig="$roleConfig" />

        </div>

        <x-accounts.account-sheet />
        <x-accounts.delete-modal />

        <x-accounts.scripts />
    @livewireScripts
    </body>
</html>