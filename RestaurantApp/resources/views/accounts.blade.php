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
        <x-ui.confirm-modal />

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5">

            <!-- ── Page header ───────────────────────────────── -->
            <x-ui.page-header title="Account Management" subtitle="Manage staff accounts — Molveno Lake Resort">
                <x-slot:actions>
                    @if($activeTab === 'users')
                        <x-ui.button onclick="openSheet()" title="Create a new staff account">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12h14"/>
                            </svg>
                            Add Account
                        </x-ui.button>
                    @endif
                </x-slot:actions>
            </x-ui.page-header>

            <!-- ── Error flash ───────────────────────────────── -->
            @if(session('error'))
                <div class="flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700"
                     x-data x-init="setTimeout(() => $el.remove(), 5000)">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- ── Page tabs (Users / Roles) ─────────────────── -->
            <div class="flex gap-1 border-b border-gray-200">
                <a href="{{ route('accounts.index', ['tab' => 'users']) }}"
                   title="Browse and edit staff accounts"
                   @class([
                       'px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-colors',
                       'border-molveno-blue-500 text-molveno-blue-600' => $activeTab === 'users',
                       'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $activeTab !== 'users',
                   ])>
                    Users
                </a>
                <a href="{{ route('accounts.index', ['tab' => 'roles']) }}"
                   title="Define roles and toggle which permissions each role grants"
                   @class([
                       'px-4 py-2.5 text-sm font-semibold border-b-2 -mb-px transition-colors',
                       'border-molveno-blue-500 text-molveno-blue-600' => $activeTab === 'roles',
                       'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $activeTab !== 'roles',
                   ])>
                    Roles &amp; Permissions
                </a>
            </div>

            @if($activeTab === 'users')

                <!-- ── Users view header ──────────────────────── -->
                <div class="flex items-center gap-2 -mb-2">
                    <h2 class="text-lg font-bold text-gray-900">Users</h2>
                    <button
                        type="button"
                        x-data
                        x-on:click="$dispatch('open-sheet', { name: 'help-accounts-users' })"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-primary hover:bg-gray-100 transition-colors"
                        title="How the Users tab works"
                        aria-label="Open users help"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01"/>
                            <circle cx="12" cy="20" r="1" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
                <x-help.sheet page="accounts-users" title="How the Users tab works" />

                <!-- ── Role filter tabs ───────────────────────── -->
                <x-accounts.role-tabs :counts="$counts" :roles="$roles" />

                <!-- ── User table ─────────────────────────────── -->
                <x-accounts.user-table :users="$users" :roleConfig="$roleConfig" />

            @else

                <!-- ── Role & Permission management ──────────── -->
                @livewire('role-management')

            @endif

        </div>

        @if($activeTab === 'users')
            <x-accounts.account-sheet :roles="$roles" />
            <x-ui.form id="delete-form" method="DELETE" class="hidden" />
            <x-accounts.scripts />
        @endif

    @livewireScripts
    </body>
</html>
