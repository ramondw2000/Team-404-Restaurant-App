@props([
    'showReservationModal' => false,
    'reservationDatetime'  => '',
    'reservationPartySize' => 2,
    'availableTables'      => collect(),
])

@if($showReservationModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" @keydown.escape.window="$wire.closeReservationModal()">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeReservationModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">New Reservation</h2>
                <p class="text-sm text-gray-500 mb-5">Fill in the details to create a reservation.</p>

                <form wire:submit="createReservation" class="space-y-4">

                    {{-- Guest Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Guest Name</label>
                        <input
                            type="text"
                            wire:model="reservationGuestName"
                            placeholder="e.g. John Smith"
                            autofocus
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        @error('reservationGuestName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone & Email --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                            <input
                                type="tel"
                                wire:model="reservationPhone"
                                placeholder="+31 6..."
                                pattern="[\+]?[\d\s\-\.\(\)]{7,}"
                                title="Enter a valid phone number (e.g. +31 6 12345678)"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('reservationPhone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                            <input
                                type="email"
                                wire:model="reservationEmail"
                                placeholder="guest@email.com"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('reservationEmail')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Party Size & Date/Time --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Party Size</label>
                            <input
                                type="number"
                                wire:model.live="reservationPartySize"
                                min="2"
                                max="20"
                                step="2"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('reservationPartySize')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Room</label>
                            <input
                                type="text"
                                wire:model="reservationRoomNumber"
                                placeholder="e.g. 12"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                        </div>
                    </div>

                    {{-- Date & Time --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date & Time</label>
                        <input
                            type="datetime-local"
                            wire:model.live="reservationDatetime"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        @error('reservationDatetime')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Table picker --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Table</label>
                        @if($reservationDatetime && $reservationPartySize >= 1)
                            @if($availableTables->isEmpty())
                                <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2.5">
                                    No available tables for this time and party size.
                                </p>
                            @else
                                <select
                                    wire:model="reservationFloorPlanElementId"
                                    class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">No table preference</option>
                                    @foreach($availableTables as $table)
                                        <option value="{{ $table->id }}">{{ $table->table_name }} — {{ $table->seat_count }} seats</option>
                                    @endforeach
                                </select>
                            @endif
                        @else
                            <div class="w-full px-3.5 py-2.5 text-sm text-gray-400 bg-gray-50 border border-gray-200 rounded-xl cursor-not-allowed">
                                Fill in party size and time first
                            </div>
                        @endif
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea
                            wire:model="reservationNotes"
                            rows="2"
                            placeholder="Dietary requirements, special occasion..."
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        ></textarea>
                        @error('reservationNotes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <x-ui.button type="button" variant="secondary" wire:click="closeReservationModal" class="flex-1 justify-center">
                            Cancel
                        </x-ui.button>
                        <x-ui.button type="submit" class="flex-1 justify-center">
                            Create Reservation
                        </x-ui.button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endif
