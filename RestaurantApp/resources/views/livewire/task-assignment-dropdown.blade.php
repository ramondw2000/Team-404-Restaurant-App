<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    {{-- Closed state --}}
    <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm rounded-lg px-2 py-1 hover:bg-gray-100 transition-colors min-w-0">
        @if($assignedUserId)
            @php
                $assignedUser = \App\Models\User::find($assignedUserId);
                $displayName = $assignedUser?->name ?? 'Nonexistent User';
            @endphp
            <x-ui.avatar :name="$displayName" size="sm" />
            <span class="truncate max-w-[120px] text-gray-700">
                {{ $displayName }}
                @if($assignedUserId === auth()->id())
                    <span class="text-gray-400">(You)</span>
                @endif
            </span>
        @else
            <x-ui.badge variant="neutral" size="sm">Unassigned</x-ui.badge>
        @endif
        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" x-cloak class="absolute z-30 mt-1 left-0 w-64 bg-white rounded-xl shadow-lg border border-gray-200 py-1 max-h-72 flex flex-col">
        {{-- Search --}}
        <div class="px-3 py-2 border-b border-gray-100">
            <input type="text" wire:model.live.debounce.300ms="userSearch" placeholder="Search users…" class="w-full text-sm border border-gray-200 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-molveno-blue-300 focus:border-molveno-blue-400 placeholder:text-gray-400" />
        </div>

        <div class="overflow-y-auto flex-1">
            {{-- Unassign option --}}
            @if($assignedUserId)
                <button type="button" wire:click="unassignUser" @click="open = false" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Unassign
                </button>
                <div class="border-b border-gray-100"></div>
            @endif

            {{-- User list --}}
            @forelse($this->assignableUsers as $user)
                <button type="button" wire:click="assignUser({{ $user->id }})" @click="open = false" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center gap-2.5 transition-colors {{ $assignedUserId === $user->id ? 'bg-molveno-blue-50' : '' }}">
                    <x-ui.avatar :name="$user->name" size="sm" />
                    <span class="truncate">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                            <span class="text-gray-400">(You)</span>
                        @endif
                    </span>
                    @if($assignedUserId === $user->id)
                        <svg class="w-4 h-4 text-molveno-blue-500 ml-auto shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </button>
            @empty
                <div class="px-3 py-4 text-sm text-gray-400 text-center">No users found</div>
            @endforelse
        </div>
    </div>
</div>
