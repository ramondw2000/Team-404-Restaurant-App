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
        <style>
            /* ── Sheet (legacy JS-driven open/close) ─────────────── */
            .sheet-overlay {
                opacity: 0; pointer-events: none;
                transition: opacity 0.3s ease;
            }
            .sheet-overlay.open { opacity: 1; pointer-events: auto; }
            .sheet-panel {
                transform: translateX(100%);
                transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
                height: 100vh; height: 100dvh;
            }
            .sheet-panel.open { transform: translateX(0); }
        </style>
    </head>
    <body class="font-sans antialiased min-h-screen bg-[#eaf4fa]">
        @include('layouts.navigation')

        @php
        $counts = [
            'all'          => $users->count(),
            'management'   => $users->filter(fn($u) => $u->hasRole('management'))->count(),
            'server'       => $users->filter(fn($u) => $u->hasRole('server'))->count(),
            'chef'         => $users->filter(fn($u) => $u->hasRole('chef'))->count(),
            'receptionist' => $users->filter(fn($u) => $u->hasRole('receptionist'))->count(),
        ];
        @endphp

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
            <x-ui.tab-group>
                <x-ui.tab :active="true"  :count="$counts['all']"          value="all"          onclick="switchTab(this)" data-role="all">All</x-ui.tab>
                <x-ui.tab :active="false" :count="$counts['management']"   value="management"   onclick="switchTab(this)" data-role="management">Management</x-ui.tab>
                <x-ui.tab :active="false" :count="$counts['server']"       value="server"       onclick="switchTab(this)" data-role="server">Server</x-ui.tab>
                <x-ui.tab :active="false" :count="$counts['chef']"         value="chef"         onclick="switchTab(this)" data-role="chef">Chef</x-ui.tab>
                <x-ui.tab :active="false" :count="$counts['receptionist']" value="receptionist" onclick="switchTab(this)" data-role="receptionist">Receptionist</x-ui.tab>
            </x-ui.tab-group>

            <!-- ── User table ─────────────────────────────────── -->
            <x-ui.card padding="none">
                <x-ui.table>
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-left">
                            <x-ui.th>User</x-ui.th>
                            <x-ui.th class="hidden sm:table-cell">Email</x-ui.th>
                            <x-ui.th>Role</x-ui.th>
                            <x-ui.th class="hidden md:table-cell">Since</x-ui.th>
                            <x-ui.th align="right">Actions</x-ui.th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <x-accounts.user-row :user="$user" :roleConfig="$roleConfig" />
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state title="No accounts found.">
                                        <x-slot:icon>
                                            <svg class="w-10 h-10 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                        </x-slot:icon>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
                <!-- Client-side empty state for tab filter -->
                <div id="no-users" class="hidden border-t border-gray-50">
                    <x-ui.empty-state title="No accounts match this filter.">
                        <x-slot:icon>
                            <svg class="w-10 h-10 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </x-slot:icon>
                    </x-ui.empty-state>
                </div>
            </x-ui.card>

        </div>

        <x-accounts.account-sheet />
        <x-accounts.delete-modal />

        <script>
            const roleDescriptions = {
                management:   'Full access to all features including account management, reports, and settings.',
                server:       'Can view and manage orders, mark dishes as served, and access the KDS.',
                chef:         'Can view the kitchen order queue and update dish preparation status.',
                receptionist: 'Can manage table assignments, room service requests, and guest check-in.',
            };

            // ── Tab filter ────────────────────────────────────────
            const TAB_ACTIVE   = ['bg-molveno-blue-500', 'border-molveno-blue-500', 'text-white'];
            const TAB_INACTIVE = ['bg-white', 'border-gray-200', 'text-gray-600', 'hover:border-molveno-blue-300', 'hover:text-molveno-blue-700'];
            const TAB_COUNT_ACTIVE   = ['bg-white/25', 'text-white'];
            const TAB_COUNT_INACTIVE = ['bg-gray-100', 'text-gray-500'];

            function switchTab(btn) {
                const role = btn.dataset.role;
                btn.closest('[class*="flex"]').querySelectorAll('button').forEach(b => {
                    const isActive = b === btn;
                    b.classList.remove(...TAB_ACTIVE, ...TAB_INACTIVE);
                    b.classList.add(...(isActive ? TAB_ACTIVE : TAB_INACTIVE));
                    const countEl = b.querySelector('span');
                    if (countEl) {
                        countEl.classList.remove(...TAB_COUNT_ACTIVE, ...TAB_COUNT_INACTIVE);
                        countEl.classList.add(...(isActive ? TAB_COUNT_ACTIVE : TAB_COUNT_INACTIVE));
                    }
                });
                let visible = 0;
                document.querySelectorAll('.user-row').forEach(row => {
                    const roles = JSON.parse(row.dataset.roles || '[]');
                    const show  = role === 'all' || roles.includes(role);
                    row.style.display = show ? '' : 'none';
                    if (show) visible++;
                });
                document.getElementById('no-users').classList.toggle('hidden', visible > 0);
            }

            // ── Sheet ─────────────────────────────────────────────
            function setRoleCheckboxes(roles) {
                document.querySelectorAll('.role-checkbox').forEach(cb => {
                    cb.checked = roles.includes(cb.value);
                });
            }

            function openSheet() {
                document.getElementById('sheet-title').textContent   = 'Add Account';
                document.getElementById('sheet-submit').textContent  = 'Save Account';
                document.getElementById('account-form').action       = '{{ route('accounts.store') }}';
                document.getElementById('form-method').value         = 'POST';
                document.getElementById('form-account-id').value     = '';
                document.getElementById('field-name').value          = '';
                document.getElementById('field-email').value         = '';
                document.getElementById('field-password').value      = '';
                setRoleCheckboxes([]);
                document.getElementById('password-hint').classList.add('hidden');
                updateRoleDesc();
                document.getElementById('sheet-overlay').classList.add('open');
                document.getElementById('sheet-panel').classList.add('open');
                document.getElementById('field-name').focus();
            }

            function openEditSheet(id, name, email, roles) {
                document.getElementById('sheet-title').textContent   = 'Edit Account';
                document.getElementById('sheet-submit').textContent  = 'Update Account';
                document.getElementById('account-form').action       = `/accounts/${id}`;
                document.getElementById('form-method').value         = 'PUT';
                document.getElementById('form-account-id').value     = id;
                document.getElementById('field-name').value          = name;
                document.getElementById('field-email').value         = email;
                document.getElementById('field-password').value      = '';
                setRoleCheckboxes(Array.isArray(roles) ? roles : [roles]);
                document.getElementById('password-hint').classList.remove('hidden');
                updateRoleDesc();
                document.getElementById('sheet-overlay').classList.add('open');
                document.getElementById('sheet-panel').classList.add('open');
            }

            function closeSheet() {
                document.getElementById('sheet-overlay').classList.remove('open');
                document.getElementById('sheet-panel').classList.remove('open');
            }

            function updateRoleDesc() {
                const checked = [...document.querySelectorAll('.role-checkbox:checked')].map(cb => cb.value);
                const desc    = document.getElementById('role-desc');
                if (checked.length > 0) {
                    desc.textContent = checked.map(r => roleDescriptions[r]).filter(Boolean).join(' ');
                    desc.classList.remove('hidden');
                } else {
                    desc.classList.add('hidden');
                }
            }

            // ── Delete modal ──────────────────────────────────────
            function confirmDelete(id, name) {
                document.getElementById('delete-msg').textContent =
                    `Are you sure you want to delete the account for "${name}"? This action cannot be undone.`;
                document.getElementById('delete-form').action = `/accounts/${id}`;
                document.getElementById('delete-confirm-btn').onclick = () => {
                    document.getElementById('delete-form').submit();
                };
                const overlay = document.getElementById('delete-overlay');
                const modal   = document.getElementById('delete-modal');
                overlay.classList.add('open');
                modal.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
                modal.classList.add('scale-100', 'opacity-100');
            }

            function closeDelete() {
                const overlay = document.getElementById('delete-overlay');
                const modal   = document.getElementById('delete-modal');
                overlay.classList.remove('open');
                modal.classList.remove('scale-100', 'opacity-100');
                modal.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
            }

            // ── Auto-open sheet on validation error ───────────────
            @if($errors->any())
                @if(old('_method') === 'PUT')
                    openEditSheet(
                        '{{ old('_account_id') }}',
                        '{{ addslashes(old('name', '')) }}',
                        '{{ addslashes(old('email', '')) }}',
                        {!! json_encode(old('roles', [])) !!}
                    );
                @else
                    openSheet();
                    document.getElementById('field-name').value  = '{{ addslashes(old('name', '')) }}';
                    document.getElementById('field-email').value = '{{ addslashes(old('email', '')) }}';
                    setRoleCheckboxes({!! json_encode(old('roles', [])) !!});
                    updateRoleDesc();
                @endif
            @endif
        </script>
    @livewireScripts
    </body>
</html>