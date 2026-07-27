@props(['reservation'])

<div
    wire:click="selectReservation({{ $reservation->id }})"
    x-data="{ localStatus: '{{ $reservation->status }}' }"
    class="group flex items-center gap-3 px-1 py-2.5 cursor-pointer hover:bg-gray-50 rounded-lg transition-colors duration-100"
>
    {{-- Exact time --}}
    <span class="w-12 shrink-0 text-xs font-bold text-molveno-blue-600 tabular-nums text-right">
        {{ $reservation->reservation_datetime->format('H:i') }}
    </span>

    {{-- Guest name --}}
    <p class="w-36 shrink-0 text-sm font-semibold text-gray-900 truncate">
        {{ $reservation->guest_name }}
    </p>

    {{-- Meta pills --}}
    <div class="flex items-center gap-2 flex-1 min-w-0 overflow-hidden">
        {{-- Party size --}}
        <span class="inline-flex items-center gap-1 text-xs text-gray-500 shrink-0">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            {{ $reservation->party_size }}
        </span>

        @if($reservation->table_number)
            <span class="inline-flex items-center gap-1 text-xs text-gray-500 shrink-0">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="10" rx="2"/><path d="M6 7V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2"/><line x1="12" y1="12" x2="12" y2="12"/>
                </svg>
                {{ $reservation->table_number }}
            </span>
        @endif

        @if($reservation->room_number)
            <span class="inline-flex items-center gap-1 text-xs text-gray-400 shrink-0">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                {{ $reservation->room_number }}
            </span>
        @endif
    </div>

    {{-- Status badge (optimistic) --}}
    <span class="ml-auto shrink-0">
        <x-reservations.status-badge :status="$reservation->status" x-bind:data-status="localStatus" />
    </span>
</div>
