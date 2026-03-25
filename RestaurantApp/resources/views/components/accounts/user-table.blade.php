@props(['users', 'roleConfig'])

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
