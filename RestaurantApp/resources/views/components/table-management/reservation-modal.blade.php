@props([
    'show' => false,
    'elementId' => null,
    'tableName' => '',
])

@if($show)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" @keydown.escape.window="$wire.closeReservationModal()">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeReservationModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">New Reservation</h2>
                <p class="text-sm text-gray-500 mb-5">Reserve {{ $tableName }} for a guest.</p>

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

                    {{-- Party Size & DateTime --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Party Size</label>
                            <input
                                type="number"
                                wire:model="reservationPartySize"
                                min="1"
                                max="20"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('reservationPartySize')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Date & Time</label>
                            <input
                                type="datetime-local"
                                wire:model="reservationDatetime"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                            @error('reservationDatetime')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
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
