<div
    x-data="{ show: false }"
    x-on:open-sheet.window="if ($event.detail.name === 'edit-reservation') { show = true; document.body.style.overflow = 'hidden'; }"
    x-on:close-sheet.window="if ($event.detail.name === 'edit-reservation') { show = false; document.body.style.overflow = ''; }"
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
                <h2 class="text-lg font-bold text-gray-900 mb-1">Edit Reservation</h2>
                <p class="text-sm text-gray-500 mb-5">Update the reservation details.</p>

                <form wire:submit="updateReservation" class="space-y-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Guest Name</label>
                        <input type="text" wire:model="editGuestName"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('editGuestName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                            <input type="tel" wire:model="editPhone"
                                placeholder="+31 6..."
                                pattern="[\+]?[\d\s\-\.\(\)]{7,}"
                                title="Enter a valid phone number (e.g. +31 6 12345678)"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('editPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                            <input type="email" wire:model="editEmail"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Party Size</label>
                            <input type="number" wire:model="editPartySize" min="2" max="20" step="2"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('editPartySize') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Room</label>
                            <input type="text" wire:model="editRoomNumber"
                                class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date & Time</label>
                        <input type="datetime-local" wire:model="editDatetime"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @error('editDatetime') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                        <select wire:model="editStatus"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="scheduled">Scheduled</option>
                            <option value="arrived">Arrived</option>
                            <option value="departed">Departed</option>
                            <option value="late">Late</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no_show">No Show</option>
                            <option value="optional">Optional</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea wire:model="editNotes" rows="2"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <x-ui.button type="button" variant="secondary" x-on:click="show = false; $dispatch('open-sheet', { name: 'detail-reservation' });" class="flex-1 justify-center">← Back</x-ui.button>
                        <x-ui.button type="button" variant="danger" wire:click="deleteReservation" wire:confirm="Delete this reservation? This cannot be undone." class="flex-1 justify-center">Delete</x-ui.button>
                        <x-ui.button type="submit" class="flex-1 justify-center">Save Changes</x-ui.button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
