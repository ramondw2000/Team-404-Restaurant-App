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
            /* ── Filter tabs ──────────────────────────────────────── */
            .tab-btn {
                display: inline-flex; align-items: center; gap: 0.375rem;
                padding: 0.375rem 0.875rem; border-radius: 9999px;
                font-size: 0.8125rem; font-weight: 600;
                border: 1px solid #e5e7eb; background: #fff; color: #6b7280;
                cursor: pointer; transition: border-color .15s, background .15s, color .15s;
                font-family: inherit; white-space: nowrap;
            }
            .tab-btn:hover { border-color: #309bcf; color: #005693; }
            .tab-btn.tab-active { background: #005693; border-color: #005693; color: #fff; }
            .tab-count {
                display: inline-flex; align-items: center; justify-content: center;
                min-width: 1.125rem; height: 1.125rem; padding: 0 0.25rem;
                border-radius: 9999px; font-size: 0.6875rem; font-weight: 700;
                background: #e5e7eb; color: #4b5563;
            }
            .tab-active .tab-count { background: rgba(255,255,255,.25); color: #fff; }

            /* ── Sheet ────────────────────────────────────────────── */
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

            /* ── Form inputs ──────────────────────────────────────── */
            .sheet-input, .sheet-select {
                width: 100%;
                border: 1px solid #e5e7eb; border-radius: 0.5rem;
                padding: 0.5rem 0.75rem; font-size: 0.875rem;
                color: #111827; background: #fff; outline: none;
                transition: border-color 0.15s, box-shadow 0.15s;
                font-family: inherit;
            }
            .sheet-input:focus, .sheet-select:focus {
                border-color: #309bcf;
                box-shadow: 0 0 0 3px rgba(48,155,207,0.2);
            }
            .sheet-input::placeholder { color: #9ca3af; }
            .sheet-input.error, .sheet-select.error { border-color: #dc2626; }
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

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5">

            <!-- ── Flash message ─────────────────────────────── -->
            @if(session('success'))
                <x-flash-message :message="session('success')" />
            @endif

            <!-- ── Page header ───────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Account Management</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Manage staff accounts &mdash; Molveno Lake Resort</p>
                </div>
                <button onclick="openSheet()"
                        class="inline-flex items-center gap-2 bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 self-start sm:self-auto">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Add Account
                </button>
            </div>

            <!-- ── Role filter tabs ───────────────────────────── -->
            <div class="flex items-center gap-2 flex-wrap">
                <button class="tab-btn tab-active" data-role="all"           onclick="switchTab(this)">All          <span class="tab-count">{{ $counts['all'] }}</span></button>
                <button class="tab-btn"            data-role="management"    onclick="switchTab(this)">Management   <span class="tab-count">{{ $counts['management'] }}</span></button>
                <button class="tab-btn"            data-role="server"        onclick="switchTab(this)">Server       <span class="tab-count">{{ $counts['server'] }}</span></button>
                <button class="tab-btn"            data-role="chef"          onclick="switchTab(this)">Chef         <span class="tab-count">{{ $counts['chef'] }}</span></button>
                <button class="tab-btn"            data-role="receptionist"  onclick="switchTab(this)">Receptionist <span class="tab-count">{{ $counts['receptionist'] }}</span></button>
            </div>

            <!-- ── User table ─────────────────────────────────── -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-left">
                            <th class="px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">User</th>
                            <th class="px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide hidden sm:table-cell">Email</th>
                            <th class="px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Role</th>
                            <th class="px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide hidden md:table-cell">Since</th>
                            <th class="px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <x-accounts.user-row :user="$user" :roleConfig="$roleConfig" />
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <svg class="mx-auto text-gray-300 mb-3" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-gray-500">No accounts found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <!-- Client-side empty state for tab filter -->
                <div id="no-users" class="hidden py-16 text-center border-t border-gray-50">
                    <svg class="mx-auto text-gray-300 mb-3" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <p class="text-sm font-semibold text-gray-500">No accounts match this filter.</p>
                </div>
            </div>

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
            function switchTab(btn) {
                const role = btn.dataset.role;
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('tab-active', b === btn));
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