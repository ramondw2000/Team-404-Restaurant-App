@props(['reservation'])

<div
    x-data="{ show: false }"
    x-on:open-sheet.window="if ($event.detail.name === 'detail-reservation') { show = true; document.body.style.overflow = 'hidden'; }"
    x-on:close-sheet.window="if ($event.detail.name === 'detail-reservation') { show = false; document.body.style.overflow = ''; }"
    x-on:keydown.escape.window="if (show) { show = false; document.body.style.overflow = ''; }"
>
    <div x-show="show" x-cloak class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"></div>

    <div
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-on:click="show = false; document.body.style.overflow = '';"
    >
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] flex flex-col" x-on:click.stop>
            <div class="p-6 overflow-y-auto flex-1">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Reservation Details</h2>

                @if($reservation)
                    @php $dt = \Carbon\Carbon::parse($reservation['reservation_datetime']); @endphp

                    {{-- Guest header --}}
                    <div class="flex items-center gap-3 mb-5 mt-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-molveno-blue-500 text-sm font-bold text-white">
                            {{ strtoupper(substr($reservation['guest_name'], 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-gray-900 truncate">{{ $reservation['guest_name'] }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $dt->format('l, j F · H:i') }}</p>
                        </div>
                    </div>

                    {{-- Status stepper --}}
                    <div class="mb-5 p-4 rounded-xl border border-gray-100 bg-gray-50">
                        <x-reservations.status-stepper
                            :status="$reservation['status']"
                            :reservation-id="$reservation['id']"
                        />
                    </div>

                    {{-- Details grid --}}
                    <div class="rounded-xl border border-gray-100 divide-y divide-gray-100 mb-4">
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm text-gray-500">Guests</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $reservation['party_size'] }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-3">
                            <span class="text-sm text-gray-500">Status</span>
                            <x-reservations.status-badge :status="$reservation['status']" />
                        </div>
                        @if($reservation['table_number'])
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-gray-500">Table</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">{{ $reservation['table_number'] }}</span>
                            </div>
                        @endif
                        @if($reservation['room_number'])
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-gray-500">Room</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $reservation['room_number'] }}</span>
                            </div>
                        @endif
                        @if($reservation['phone'])
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-gray-500">Phone</span>
                                <a href="tel:{{ $reservation['phone'] }}" class="text-sm font-semibold text-molveno-blue-600 hover:underline">{{ $reservation['phone'] }}</a>
                            </div>
                        @endif
                        @if($reservation['email'])
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-gray-500">Email</span>
                                <span class="text-sm font-semibold text-gray-900 truncate max-w-[160px]">{{ $reservation['email'] }}</span>
                            </div>
                        @endif
                        @if($reservation['created_at'])
                            <div class="flex items-center justify-between px-4 py-3">
                                <span class="text-sm text-gray-500">Created</span>
                                <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($reservation['created_at'])->format('d M Y, H:i') }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Internal notes --}}
                    @if($reservation['internal_notes'])
                        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
                            <p class="text-xs font-bold text-blue-700 mb-1 uppercase tracking-wide">Internal Note</p>
                            <p class="text-sm text-blue-900">{{ $reservation['internal_notes'] }}</p>
                        </div>
                    @endif
                @else
                    <x-ui.empty-state title="No reservation selected" description="Click a reservation to view details." />
                @endif
            </div>

            @if($reservation)
                <div class="shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100">
                    <x-ui.button variant="secondary" x-on:click="show = false; document.body.style.overflow = '';">Cancel</x-ui.button>
                    <x-ui.button variant="danger" wire:click="deleteReservation" wire:confirm="Delete this reservation? This cannot be undone.">Delete</x-ui.button>
                    <x-ui.button wire:click="openEditSheet({{ $reservation['id'] }})">Edit Reservation</x-ui.button>
                </div>
            @endif
        </div>
    </div>
</div>
