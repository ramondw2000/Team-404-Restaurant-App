@props([
    'reservations' => collect(),
])

@if($reservations->isNotEmpty())
    <div>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Today's Reservations</p>
        <div class="space-y-2">
            @foreach($reservations as $reservation)
                @php
                    $statusColor = match($reservation->status) {
                        'scheduled' => 'bg-blue-100 text-blue-800',
                        'arrived' => 'bg-green-100 text-green-800',
                        'departed' => 'bg-gray-100 text-gray-600',
                        'late' => 'bg-amber-100 text-amber-800',
                        'no_show' => 'bg-rose-100 text-rose-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                        default => 'bg-gray-100 text-gray-600',
                    };
                    $dotColor = match($reservation->status) {
                        'scheduled' => 'bg-blue-500',
                        'arrived' => 'bg-green-500',
                        'departed' => 'bg-gray-400',
                        'late' => 'bg-amber-500',
                        'no_show' => 'bg-rose-500',
                        'cancelled' => 'bg-red-500',
                        default => 'bg-gray-400',
                    };
                    $isLate = $reservation->status === 'scheduled' && $reservation->reservation_datetime->isPast();
                    $minutesLate = $isLate ? $reservation->reservation_datetime->diffInMinutes(now()) : 0;
                    $canNoShow = $isLate && $minutesLate >= 30;
                @endphp
                <div class="bg-gray-50 rounded-xl p-3.5 space-y-2 {{ $isLate ? 'ring-1 ring-amber-200' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $reservation->guest_name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $reservation->reservation_datetime->format('H:i') }}
                                &middot; {{ $reservation->party_size }} guests
                                @if($isLate)
                                    <span class="text-amber-600 font-medium">({{ $minutesLate }}m late)</span>
                                @endif
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusColor }} shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                            {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                        </span>
                    </div>

                    @if($reservation->internal_notes)
                        <p class="text-xs text-gray-400 italic">{{ $reservation->internal_notes }}</p>
                    @endif

                    {{-- Action buttons based on status --}}
                    <div class="flex flex-wrap gap-2">
                        @if($reservation->status === 'scheduled')
                            <button
                                wire:click="seatReservation({{ $reservation->id }})"
                                class="flex-1 py-1.5 px-3 text-xs font-semibold text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors"
                            >
                                Seat Guest
                            </button>
                            <button
                                wire:click="cancelReservation({{ $reservation->id }})"
                                wire:confirm="Cancel this reservation? This action cannot be undone."
                                class="py-1.5 px-3 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors"
                            >
                                Cancel
                            </button>
                        @endif
                        @if($canNoShow)
                            <button
                                wire:click="markNoShow({{ $reservation->id }})"
                                class="flex-1 py-1.5 px-3 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition-colors"
                            >
                                No Show (30m+ late)
                            </button>
                        @endif
                        @if($reservation->status === 'arrived')
                            <button
                                wire:click="openDepartureConfirm({{ $reservation->id }})"
                                class="flex-1 py-1.5 px-3 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Mark Departed
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="bg-gray-50 rounded-xl p-4 text-center">
        <p class="text-xs text-gray-400">No reservations for today</p>
    </div>
@endif
