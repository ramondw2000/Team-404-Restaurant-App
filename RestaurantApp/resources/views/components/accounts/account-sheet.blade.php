@props(['roles'])

<!-- ── Sheet overlay ──────────────────────────────────────── -->
<div id="sheet-overlay"
     class="sheet-overlay fixed inset-0 bg-black/40 z-40"
     onclick="closeSheet()"></div>

<!-- ── Add / Edit sheet panel ─────────────────────────────── -->
<div id="sheet-panel"
     class="sheet-panel fixed top-0 right-0 z-50 w-full max-w-md bg-white shadow-2xl flex flex-col">

    <!-- Sheet header -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-2">
            <h2 id="sheet-title" class="text-base font-bold text-gray-900">Add Account</h2>
            <button
                type="button"
                x-data
                x-on:click.stop="$dispatch('open-sheet', { name: 'help-accounts-sheet' })"
                class="p-1 rounded-lg text-gray-400 hover:text-primary hover:bg-gray-100 transition-colors"
                title="How to fill in this form"
                aria-label="Open account form help"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01"/>
                    <circle cx="12" cy="20" r="1" fill="currentColor"/>
                </svg>
            </button>
        </div>
        <button onclick="closeSheet()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>
    <x-help.sheet page="accounts-sheet" title="How to fill in the account form" />

    <!-- Sheet form -->
    <form id="account-form" method="POST" class="flex flex-col flex-1 overflow-hidden">
        @csrf
        <input type="hidden" name="_method"     id="form-method"     value="POST">
        <input type="hidden" name="_account_id" id="form-account-id" value="">

        <div class="flex-1 overflow-y-auto px-5 py-5 flex flex-col gap-5">

            <!-- Name -->
            <x-ui.input-group label="Full Name" name="name" id="field-name" placeholder="e.g. Sofia Ricci" />

            <!-- Email -->
            <x-ui.input-group label="Email Address" name="email" type="email" id="field-email" placeholder="name@molvenoresort.it" />

            <!-- Password -->
            <x-ui.input-group label="Password" name="password" type="password" id="field-password" placeholder="Minimum 8 characters">
                <x-ui.input type="password" name="password" id="field-password" placeholder="Minimum 8 characters" :error="$errors->has('password')" />
                <p id="password-hint" class="text-xs text-gray-400 hidden">Leave blank to keep the current password.</p>
            </x-ui.input-group>

            <!-- Roles -->
            @php $oldRoles = old('roles', []); @endphp
            <div class="flex flex-col gap-2">
                <label class="text-sm font-semibold text-gray-700">Roles</label>
                <div class="flex flex-col gap-2">
                    @foreach($roles as $role)
                        @php $label = ucwords(str_replace(['_', '-'], ' ', $role->name)); @endphp
                        <label class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 cursor-pointer hover:border-molveno-blue-300 transition-colors">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                   class="role-checkbox w-4 h-4 rounded accent-molveno-blue-500"
                                   data-role="{{ $role->name }}"
                                   {{ in_array($role->name, $oldRoles) ? 'checked' : '' }}
                                   onchange="updateRoleDesc()">
                            <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p id="role-desc" class="text-xs text-gray-400 hidden"></p>
                @error('roles')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <!-- Sheet footer -->
        <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <x-ui.button type="button" variant="secondary" onclick="closeSheet()">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" id="sheet-submit">
                Save Account
            </x-ui.button>
        </div>
    </form>
</div>
