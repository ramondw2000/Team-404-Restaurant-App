<div>
    {{-- ── Success flash ───────────────────────────────────────── --}}
    @if($successMessage)
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700"
             x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            {{ $successMessage }}
        </div>
    @endif

    {{-- ── Layout: sidebar + editor ────────────────────────────── --}}
    <div class="flex flex-col lg:flex-row gap-4">

        {{-- ── Role list sidebar ───────────────────────────────── --}}
        <div class="w-full lg:w-64 shrink-0">
            <x-ui.card padding="none">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Roles</h3>
                    <button wire:click="openCreateForm"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-molveno-blue-600 hover:text-molveno-blue-700 transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                        New Role
                    </button>
                </div>

                <ul class="py-1">
                    @foreach($this->roles as $role)
                        <li wire:key="role-{{ $role->id }}">
                            <button
                                wire:click="selectRole({{ $role->id }})"
                                @class([
                                    'w-full flex items-center gap-3 px-4 py-2.5 text-left transition-colors group',
                                    'bg-molveno-blue-50' => $selectedRoleId === $role->id,
                                    'hover:bg-gray-50' => $selectedRoleId !== $role->id,
                                ])
                            >
                                <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ \App\Livewire\RoleManagement::COLORS[$role->color] ?? 'bg-slate-500' }}"></span>
                                <span @class([
                                    'text-sm font-medium flex-1 truncate',
                                    'text-molveno-blue-700' => $selectedRoleId === $role->id,
                                    'text-gray-700' => $selectedRoleId !== $role->id,
                                ])>
                                    {{ ucwords(str_replace(['_', '-'], ' ', $role->name)) }}
                                </span>
                                @if($role->is_administrator)
                                    <span class="text-[0.6rem] font-bold uppercase tracking-wide text-purple-500 bg-purple-50 px-1.5 py-0.5 rounded">
                                        Admin
                                    </span>
                                @endif
                            </button>
                        </li>
                    @endforeach

                    @if($this->roles->isEmpty())
                        <li class="px-4 py-6 text-center text-sm text-gray-400">No roles yet.</li>
                    @endif
                </ul>
            </x-ui.card>
        </div>

        {{-- ── Role editor / create form ────────────────────────── --}}
        <div class="flex-1 min-w-0">

            {{-- Create / Edit role form --}}
            @if($showRoleForm)
                <x-ui.card>
                    <h3 class="text-base font-bold text-gray-900 mb-4">
                        {{ $editingRoleId ? 'Edit Role' : 'Create New Role' }}
                    </h3>

                    <div class="flex flex-col gap-4">
                        {{-- Name --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-semibold text-gray-700" for="role-name">Role Name</label>
                            <input wire:model="formName"
                                   id="role-name"
                                   type="text"
                                   placeholder="e.g. Head Chef"
                                   class="w-full rounded-lg border px-3 py-2 text-sm text-gray-900 placeholder-gray-400 outline-none transition focus:ring-2 focus:ring-molveno-blue-400 @error('formName') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                            @error('formName')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Color picker --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-semibold text-gray-700">Color</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(\App\Livewire\RoleManagement::COLORS as $colorName => $colorClass)
                                    <button type="button"
                                            wire:click="$set('formColor', '{{ $colorName }}')"
                                            title="{{ ucfirst($colorName) }}"
                                            @class([
                                                'w-7 h-7 rounded-full transition-all ring-offset-2',
                                                $colorClass,
                                                'ring-2 ring-gray-900 scale-110' => $formColor === $colorName,
                                                'ring-2 ring-transparent hover:scale-110' => $formColor !== $colorName,
                                            ])>
                                    </button>
                                @endforeach
                            </div>
                            @error('formColor')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-3 pt-1">
                            <x-ui.button wire:click="saveRole" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveRole">
                                    {{ $editingRoleId ? 'Update Role' : 'Create Role' }}
                                </span>
                                <span wire:loading wire:target="saveRole">Saving…</span>
                            </x-ui.button>
                            <x-ui.button variant="secondary" wire:click="cancelRoleForm">
                                Cancel
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.card>

            {{-- Role permission editor --}}
            @elseif($this->selectedRole)
                @php $role = $this->selectedRole; @endphp
                <x-ui.card padding="none">
                    {{-- Role header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <span class="w-3 h-3 rounded-full shrink-0 {{ \App\Livewire\RoleManagement::COLORS[$role->color] ?? 'bg-slate-500' }}"></span>
                            <h3 class="text-base font-bold text-gray-900 truncate">
                                {{ ucwords(str_replace(['_', '-'], ' ', $role->name)) }}
                            </h3>
                            @if($role->is_administrator)
                                <span class="text-xs font-bold text-purple-600 bg-purple-50 border border-purple-200 px-2 py-0.5 rounded-full">
                                    Administrator
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-ui.button variant="secondary" size="sm"
                                         wire:click="openEditForm({{ $role->id }})">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit
                            </x-ui.button>
                            @if($role->name !== 'management')
                                <x-ui.button variant="ghost" size="sm"
                                             wire:click="confirmDelete({{ $role->id }})"
                                             class="text-red-500 hover:bg-red-50">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6"/>
                                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                    Delete
                                </x-ui.button>
                            @endif
                        </div>
                    </div>

                    <div class="p-5 flex flex-col gap-6">

                        {{-- Administrator toggle --}}
                        <div class="flex items-start gap-4 p-4 rounded-xl border {{ $role->is_administrator ? 'border-purple-200 bg-purple-50' : 'border-gray-200 bg-gray-50' }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold {{ $role->is_administrator ? 'text-purple-800' : 'text-gray-800' }}">
                                    Administrator
                                </p>
                                <p class="text-xs {{ $role->is_administrator ? 'text-purple-600' : 'text-gray-500' }} mt-0.5">
                                    @if($role->name === 'management')
                                        This role is permanently locked as Administrator and cannot be changed.
                                    @else
                                        When enabled, this role bypasses all permission checks and implicitly grants every permission.
                                    @endif
                                </p>
                            </div>
                            <button
                                @if($role->name !== 'management') wire:click="toggleAdministrator" @endif
                                @class([
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors shrink-0 focus:outline-none',
                                    'bg-purple-500' => $role->is_administrator,
                                    'bg-gray-300' => ! $role->is_administrator,
                                    'cursor-not-allowed opacity-60' => $role->name === 'management',
                                    'cursor-pointer' => $role->name !== 'management',
                                ])
                                @if($role->name === 'management') disabled @endif
                                aria-label="Toggle administrator"
                            >
                                <span @class([
                                    'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                                    'translate-x-6' => $role->is_administrator,
                                    'translate-x-1' => ! $role->is_administrator,
                                ])></span>
                            </button>
                        </div>

                        {{-- Permissions (hidden when administrator) --}}
                        @if($role->is_administrator)
                            <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-purple-100 bg-purple-50/50 text-sm text-purple-700">
                                <svg class="w-4 h-4 shrink-0 text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M12 8v4M12 16h.01"/>
                                </svg>
                                This role has administrator access and implicitly holds all permissions.
                            </div>
                        @else
                            {{-- Flat permission list grouped by page --}}
                            <div class="flex flex-col gap-5">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Permissions</p>

                                @foreach(\App\Support\PermissionRegistry::GROUPS as $group)
                                    @php
                                        $viewGateOn = $group['view_gate'] === null
                                            || isset($this->rolePermissions[$group['view_gate']]);
                                    @endphp

                                    <div>
                                        {{-- Group label --}}
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                                            {{ $group['label'] }}
                                        </p>

                                        <div class="rounded-xl border border-gray-200 overflow-hidden divide-y divide-gray-100">
                                            @foreach($group['permissions'] as $perm)
                                                @php
                                                    $isViewGate = $perm['name'] === $group['view_gate'];
                                                    $isGated = ! $isViewGate && $group['view_gate'] !== null;
                                                    $isDisabled = $isGated && ! $viewGateOn;
                                                    $isGranted = isset($this->rolePermissions[$perm['name']]);
                                                @endphp

                                                <button
                                                    @if(! $isDisabled)
                                                        wire:click="togglePermission('{{ $perm['name'] }}')"
                                                    @endif
                                                    wire:key="perm-{{ $role->id }}-{{ $perm['name'] }}"
                                                    @class([
                                                        'w-full flex items-center gap-3 px-4 py-3 text-left transition-colors',
                                                        'opacity-40 cursor-not-allowed' => $isDisabled,
                                                        'cursor-pointer' => ! $isDisabled,
                                                        'hover:bg-gray-50' => ! $isDisabled && ! $isGranted,
                                                        'bg-molveno-blue-50 hover:bg-molveno-blue-100' => ! $isDisabled && $isGranted,
                                                        'bg-gray-50/50' => $isViewGate && ! $isGranted,
                                                    ])
                                                    @if($isDisabled) disabled @endif
                                                >
                                                    {{-- Toggle switch --}}
                                                    <span @class([
                                                        'relative inline-flex h-5 w-9 items-center rounded-full transition-colors shrink-0',
                                                        'bg-molveno-blue-500' => $isGranted,
                                                        'bg-gray-200' => ! $isGranted,
                                                    ])>
                                                        <span @class([
                                                            'inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform',
                                                            'translate-x-4' => $isGranted,
                                                            'translate-x-0.5' => ! $isGranted,
                                                        ])></span>
                                                    </span>

                                                    {{-- Permission label + description --}}
                                                    <div class="flex-1 min-w-0">
                                                        <span @class([
                                                            'text-sm',
                                                            'font-bold text-gray-800' => $isViewGate,
                                                            'font-medium text-gray-700' => ! $isViewGate && ! $isDisabled,
                                                            'text-gray-400' => $isDisabled,
                                                        ])>{{ $perm['name'] }}</span>
                                                        <span class="text-xs text-gray-400 ml-1.5">— {{ $perm['description'] }}</span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-ui.card>

            @else
                <x-ui.card>
                    <x-ui.empty-state title="Select a role to manage its permissions.">
                        <x-slot:icon>
                            <svg class="w-10 h-10 text-gray-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </x-slot:icon>
                    </x-ui.empty-state>
                </x-ui.card>
            @endif
        </div>
    </div>

    {{-- ── Delete confirmation modal ──────────────────────────── --}}
    @if($deleteConfirmRoleId)
        @php $deleteRole = \App\Models\Role::find($deleteConfirmRoleId); @endphp
        @if($deleteRole)
            <div class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" wire:click="cancelDelete"></div>
                <div class="relative z-10 bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 animate-in fade-in zoom-in-95 duration-200">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6"/>
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Delete Role</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Are you sure you want to delete the <strong>{{ ucwords(str_replace(['_', '-'], ' ', $deleteRole->name)) }}</strong> role?
                                It will be removed from all users currently assigned to it.
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <x-ui.button variant="secondary" size="sm" wire:click="cancelDelete">
                            Cancel
                        </x-ui.button>
                        <x-ui.button variant="danger" size="sm" wire:click="deleteRole">
                            Delete Role
                        </x-ui.button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
