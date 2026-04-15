<x-ui.sheet name="edit-reservation" title="Edit Reservation" subtitle="Update the reservation details." maxWidth="md">
    <div class="flex flex-col gap-4">

        <div class="flex flex-col gap-1.5">
            <label for="edit_guest_name" class="text-sm font-semibold text-gray-700">Guest Name <span class="text-red-400">*</span></label>
            <input
                type="text"
                id="edit_guest_name"
                wire:model="editGuestName"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300 @error('editGuestName') border-red-400 @enderror"
            >
            @error('editGuestName')
                <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label for="edit_phone" class="text-sm font-semibold text-gray-700">Phone</label>
                <input
                    type="tel"
                    id="edit_phone"
                    wire:model="editPhone"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"
                >
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="edit_email" class="text-sm font-semibold text-gray-700">Email</label>
                <input
                    type="email"
                    id="edit_email"
                    wire:model="editEmail"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"
                >
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label for="edit_party_size" class="text-sm font-semibold text-gray-700">Guests <span class="text-red-400">*</span></label>
                <input
                    type="number"
                    id="edit_party_size"
                    wire:model="editPartySize"
                    min="1"
                    max="20"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300 @error('editPartySize') border-red-400 @enderror"
                >
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="edit_room_number" class="text-sm font-semibold text-gray-700">Room</label>
                <input
                    type="text"
                    id="edit_room_number"
                    wire:model="editRoomNumber"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"
                >
            </div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="edit_datetime" class="text-sm font-semibold text-gray-700">Date & Time <span class="text-red-400">*</span></label>
            <input
                type="datetime-local"
                id="edit_datetime"
                wire:model="editDatetime"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300 @error('editDatetime') border-red-400 @enderror"
            >
            @error('editDatetime')
                <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="edit_status" class="text-sm font-semibold text-gray-700">Status <span class="text-red-400">*</span></label>
            <select
                id="edit_status"
                wire:model="editStatus"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"
            >
                <option value="scheduled">Scheduled</option>
                <option value="arrived">Arrived</option>
                <option value="departed">Departed</option>
                <option value="late">Late</option>
                <option value="cancelled">Cancelled</option>
                <option value="no_show">No Show</option>
                <option value="optional">Optional</option>
            </select>
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="edit_notes" class="text-sm font-semibold text-gray-700">Internal Notes</label>
            <textarea
                id="edit_notes"
                wire:model="editNotes"
                rows="3"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 resize-none focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"
            ></textarea>
        </div>
    </div>

    <x-slot:footer>
        <x-ui.button variant="secondary" @click="$dispatch('close-sheet', 'edit-reservation')">Cancel</x-ui.button>
        <x-ui.button wire:click="updateReservation">Save Changes</x-ui.button>
    </x-slot:footer>
</x-ui.sheet>
