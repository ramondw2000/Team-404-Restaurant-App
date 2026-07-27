@props(['user', 'rc'])

@php
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
        <x-accounts.role-badge :rc="$rc" />
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
