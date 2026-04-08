{{-- Edit Reservation Sheet --}}
<x-ui.sheet name="edit-reservation" title="Edit Reservation" subtitle="Update the reservation details." maxWidth="md">
    <template x-if="editingReservation && reservations[editingReservation]">
        <form id="edit-reservation-form" method="POST" :action="`/reservations/${editingReservation}`" class="flex flex-col gap-5">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-1.5">
                <label for="edit_guest_name" class="text-sm font-semibold text-gray-700">Guest Name <span class="text-red-400">*</span></label>
                <input type="text" name="guest_name" id="edit_guest_name" required :value="reservations[editingReservation].guest_name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label for="edit_phone" class="text-sm font-semibold text-gray-700">Phone</label>
                    <input type="tel" name="phone" id="edit_phone" :value="reservations[editingReservation].phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label for="edit_email" class="text-sm font-semibold text-gray-700">Email</label>
                    <input type="email" name="email" id="edit_email" :value="reservations[editingReservation].email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label for="edit_party_size" class="text-sm font-semibold text-gray-700">Number of Guests <span class="text-red-400">*</span></label>
                    <input type="number" name="party_size" id="edit_party_size" min="1" max="20" required :value="reservations[editingReservation].party_size" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label for="edit_reservation_datetime" class="text-sm font-semibold text-gray-700">Date & Time <span class="text-red-400">*</span></label>
                    <input type="datetime-local" name="reservation_datetime" id="edit_reservation_datetime" required x-bind:value="new Date(reservations[editingReservation].reservation_datetime).toISOString().slice(0, 16)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label for="edit_table_number" class="text-sm font-semibold text-gray-700">Table Number</label>
                    <input type="text" name="table_number" id="edit_table_number" :value="reservations[editingReservation].table_number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label for="edit_room_number" class="text-sm font-semibold text-gray-700">Room Number</label>
                    <input type="text" name="room_number" id="edit_room_number" :value="reservations[editingReservation].room_number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="edit_status" class="text-sm font-semibold text-gray-700">Status <span class="text-red-400">*</span></label>
                <select name="status" id="edit_status" required x-model="reservations[editingReservation].status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                    <option value="scheduled">Scheduled</option>
                    <option value="arrived">Arrived</option>
                    <option value="departed">Departed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="late">Late</option>
                    <option value="optional">Optional</option>
                    <option value="no_show">No Show</option>
                </select>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="edit_internal_notes" class="text-sm font-semibold text-gray-700">Internal Notes</label>
                <textarea name="internal_notes" id="edit_internal_notes" rows="3" x-text="reservations[editingReservation].internal_notes" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 resize-none focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"></textarea>
            </div>
        </form>
    </template>

    <x-slot:footer>
        <x-ui.button variant="secondary" x-on:click="$dispatch('close-sheet', 'edit-reservation')">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit" form="edit-reservation-form">
            Save Changes
        </x-ui.button>
    </x-slot:footer>
</x-ui.sheet>
