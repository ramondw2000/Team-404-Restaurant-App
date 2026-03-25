@props(['user', 'roleConfig'])

@php
    $userRoles = $user->getRoleNames()->all();
    $originalAdminId = session('impersonation.original_user_id');
    $actor = $originalAdminId ? \App\Models\User::find($originalAdminId) : Auth::user();
    $canImpersonate = $actor
        && $actor->roles()->where('is_administrator', true)->exists()
        && ! $user->roles()->where('is_administrator', true)->exists()
        && $user->id !== $actor->id;
@endphp

<tr class="user-row border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors"
    data-roles='@json($userRoles)'>
    <x-ui.td>
        <div class="flex items-center gap-3">
            <x-ui.avatar :name="$user->name" />
            <div>
                <p class="font-semibold text-gray-900 leading-snug">{{ $user->name }}</p>
                <p class="text-xs text-gray-400 sm:hidden">{{ $user->email }}</p>
            </div>
        </div>
    </x-ui.td>
    <x-ui.td class="text-gray-500 hidden sm:table-cell">{{ $user->email }}</x-ui.td>
    <x-ui.td>
        <div class="flex flex-wrap gap-1">
            @forelse($userRoles as $roleName)
                @if(isset($roleConfig[$roleName]))
                    <x-accounts.role-badge :rc="$roleConfig[$roleName]" />
                @endif
            @empty
                <span class="text-xs text-gray-400 italic">No role</span>
            @endforelse
        </div>
    </x-ui.td>
    <x-ui.td class="text-gray-400 text-xs hidden md:table-cell">
        {{ $user->created_at->format('M Y') }}
    </x-ui.td>
    <x-ui.td>
        <div class="flex items-center justify-end gap-1">
            @if($canImpersonate)
                <form method="POST" action="{{ route('impersonation.start', $user) }}">
                    @csrf
                    <x-ui.button type="submit" variant="ghost" size="sm"
                        title="Impersonate {{ $user->name }}"
                        class="p-1.5 hover:text-amber-600 hover:bg-amber-50">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                            <path d="M23 11l-4 4-2-2"/>
                        </svg>
                    </x-ui.button>
                </form>
            @endif
            <x-ui.button variant="ghost" size="sm"
                onclick="openEditSheet({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', JSON.parse(this.closest('tr').dataset.roles))"
                title="Edit"
                class="p-1.5 hover:text-molveno-blue-500 hover:bg-molveno-blue-50">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </x-ui.button>
            <x-ui.button variant="ghost" size="sm"
                onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')"
                title="Delete"
                class="p-1.5 hover:text-red-500 hover:bg-red-50">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
            </x-ui.button>
        </div>
    </x-ui.td>
</tr>