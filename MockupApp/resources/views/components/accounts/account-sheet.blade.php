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
