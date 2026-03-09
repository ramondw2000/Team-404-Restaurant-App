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
        @include('layouts.guest-navigation')

        @php
        $counts = [
            'all'          => $users->count(),
            'management'   => $users->where('role', 'management')->count(),
            'server'       => $users->where('role', 'server')->count(),
            'chef'         => $users->where('role', 'chef')->count(),
            'receptionist' => $users->where('role', 'receptionist')->count(),
        ];
        @endphp

        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5">

            <!-- ── Flash message ─────────────────────────────── -->
            @if(session('success'))
                <div id="flash-msg" class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm font-medium px-4 py-3 rounded-xl shadow-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-green-600">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                    {{ session('success') }}
                    <button onclick="document.getElementById('flash-msg').remove()" class="ml-auto text-green-500 hover:text-green-700">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
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
                            @php
                                $rc       = $roleConfig[$user->role];
                                $nameParts = explode(' ', $user->name);
                                $initials  = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                            @endphp
                            <tr class="user-row border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors"
                                data-role="{{ $user->role }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-molveno-blue-500 flex items-center justify-center shrink-0">
                                            <span class="text-white text-xs font-bold">{{ $initials }}</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 leading-snug">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-400 sm:hidden">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden sm:table-cell">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $rc['bg'] }} {{ $rc['text'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $rc['dot'] }}"></span>
                                        {{ $rc['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-400 text-xs hidden md:table-cell">
                                    {{ $user->created_at->format('M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button onclick="openEditSheet({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}')"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-molveno-blue-500 hover:bg-molveno-blue-50 transition-colors"
                                                title="Edit">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                        </button>
                                        <button onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                                title="Delete">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                                <path d="M10 11v6M14 11v6"/>
                                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
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

        <!-- ── Sheet overlay ──────────────────────────────────────── -->
        <div id="sheet-overlay"
             class="sheet-overlay fixed inset-0 bg-black/40 z-40"
             onclick="closeSheet()"></div>

        <!-- ── Add / Edit sheet panel ─────────────────────────────── -->
        <div id="sheet-panel"
             class="sheet-panel fixed top-0 right-0 z-50 w-full max-w-md bg-white shadow-2xl flex flex-col">

            <!-- Sheet header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 id="sheet-title" class="text-base font-bold text-gray-900">Add Account</h2>
                <button onclick="closeSheet()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Sheet form -->
            <form id="account-form" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="_method"     id="form-method"     value="POST">
                <input type="hidden" name="_account_id" id="form-account-id" value="">

                <div class="flex-1 overflow-y-auto px-5 py-5 flex flex-col gap-5">

                    <!-- Name -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-gray-700" for="field-name">Full Name</label>
                        <input id="field-name" name="name" type="text"
                               class="sheet-input @error('name') error @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. Sofia Ricci">
                        @error('name')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-gray-700" for="field-email">Email Address</label>
                        <input id="field-email" name="email" type="email"
                               class="sheet-input @error('email') error @enderror"
                               value="{{ old('email') }}"
                               placeholder="name@molvenoresort.it">
                        @error('email')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-gray-700" for="field-password">Password</label>
                        <input id="field-password" name="password" type="password"
                               class="sheet-input @error('password') error @enderror"
                               placeholder="Minimum 8 characters">
                        <p id="password-hint" class="text-xs text-gray-400 hidden">Leave blank to keep the current password.</p>
                        @error('password')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-gray-700" for="field-role">Role</label>
                        <select id="field-role" name="role"
                                class="sheet-select @error('role') error @enderror"
                                onchange="updateRoleDesc()">
                            <option value="">Select a role…</option>
                            <option value="management"   {{ old('role') === 'management'   ? 'selected' : '' }}>Management</option>
                            <option value="server"       {{ old('role') === 'server'       ? 'selected' : '' }}>Server</option>
                            <option value="chef"         {{ old('role') === 'chef'         ? 'selected' : '' }}>Chef</option>
                            <option value="receptionist" {{ old('role') === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                        </select>
                        <p id="role-desc" class="text-xs text-gray-400 hidden"></p>
                        @error('role')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Sheet footer -->
                <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeSheet()"
                            class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="sheet-submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-molveno-blue-500 hover:bg-molveno-blue-700 rounded-lg shadow-sm transition-colors">
                        Save Account
                    </button>
                </div>
            </form>
        </div>

        <!-- ── Hidden delete form ─────────────────────────────────── -->
        <form id="delete-form" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <!-- ── Delete confirmation modal ──────────────────────────── -->
        <div id="delete-overlay"
             class="sheet-overlay fixed inset-0 bg-black/40 z-40 flex items-center justify-center"
             onclick="closeDelete()">
        </div>
        <div id="delete-modal"
             class="fixed z-50 bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4
                    top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                    transition-all duration-200 scale-95 opacity-0 pointer-events-none">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6M14 11v6"/>
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Delete Account</h3>
                    <p id="delete-msg" class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeDelete()"
                        class="px-3 py-1.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="button" id="delete-confirm-btn"
                        class="px-3 py-1.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                    Delete
                </button>
            </div>
        </div>

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
                    const show = role === 'all' || row.dataset.role === role;
                    row.style.display = show ? '' : 'none';
                    if (show) visible++;
                });
                document.getElementById('no-users').classList.toggle('hidden', visible > 0);
            }

            // ── Sheet ─────────────────────────────────────────────
            function openSheet() {
                document.getElementById('sheet-title').textContent   = 'Add Account';
                document.getElementById('sheet-submit').textContent  = 'Save Account';
                document.getElementById('account-form').action       = '{{ route('accounts.store') }}';
                document.getElementById('form-method').value         = 'POST';
                document.getElementById('field-name').value          = '';
                document.getElementById('field-email').value         = '';
                document.getElementById('field-password').value      = '';
                document.getElementById('field-role').value          = '';
                document.getElementById('password-hint').classList.add('hidden');
                updateRoleDesc();
                document.getElementById('sheet-overlay').classList.add('open');
                document.getElementById('sheet-panel').classList.add('open');
                document.getElementById('field-name').focus();
            }

            function openEditSheet(id, name, email, role) {
                document.getElementById('sheet-title').textContent   = 'Edit Account';
                document.getElementById('sheet-submit').textContent  = 'Update Account';
                document.getElementById('account-form').action       = `/accounts/${id}`;
                document.getElementById('form-method').value         = 'PUT';
                document.getElementById('form-account-id').value     = id;
                document.getElementById('field-name').value          = name;
                document.getElementById('field-email').value         = email;
                document.getElementById('field-password').value      = '';
                document.getElementById('field-role').value          = role;
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
                const val  = document.getElementById('field-role').value;
                const desc = document.getElementById('role-desc');
                if (val && roleDescriptions[val]) {
                    desc.textContent = roleDescriptions[val];
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
                    // Reopen edit sheet — restore values from old() input
                    openEditSheet(
                        '{{ old('_account_id') }}',
                        '{{ addslashes(old('name', '')) }}',
                        '{{ addslashes(old('email', '')) }}',
                        '{{ old('role', '') }}'
                    );
                @else
                    openSheet();
                    // Restore name/email/role from old() (already set via Blade value= attributes)
                    document.getElementById('field-name').value  = '{{ addslashes(old('name', '')) }}';
                    document.getElementById('field-email').value = '{{ addslashes(old('email', '')) }}';
                    document.getElementById('field-role').value  = '{{ old('role', '') }}';
                    updateRoleDesc();
                @endif
            @endif
        </script>
    </body>
</html>
