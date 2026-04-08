{{-- Edit Reservation Modal --}}
<div x-show="showEditModal" x-cloak>
    {{-- Overlay --}}
    <div
        x-show="showEditModal"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 z-40"
        @click="showEditModal = false"
    ></div>

    {{-- Panel --}}
    <div
        x-show="showEditModal"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full sm:max-w-2xl" @click.stop>
            <template x-if="editingReservation && reservations[editingReservation]">
                <form method="POST" :action="`/reservations/${editingReservation}`">
                    @csrf
                    @method('PUT')
                    <div class="px-6 py-5">
                        <h3 class="text-lg font-bold text-gray-900 mb-5">Edit Reservation</h3>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2 flex flex-col gap-1.5">
                                <label for="edit_guest_name" class="text-sm font-semibold text-gray-700">Guest Name <span class="text-red-400">*</span></label>
                                <input type="text" name="guest_name" id="edit_guest_name" required :value="reservations[editingReservation].guest_name" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="edit_phone" class="text-sm font-semibold text-gray-700">Phone</label>
                                <input type="tel" name="phone" id="edit_phone" :value="reservations[editingReservation].phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="edit_email" class="text-sm font-semibold text-gray-700">Email</label>
                                <input type="email" name="email" id="edit_email" :value="reservations[editingReservation].email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="edit_party_size" class="text-sm font-semibold text-gray-700">Number of Guests <span class="text-red-400">*</span></label>
                                <input type="number" name="party_size" id="edit_party_size" min="1" max="20" required :value="reservations[editingReservation].party_size" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="edit_reservation_datetime" class="text-sm font-semibold text-gray-700">Date & Time <span class="text-red-400">*</span></label>
                                <input type="datetime-local" name="reservation_datetime" id="edit_reservation_datetime" required x-bind:value="new Date(reservations[editingReservation].reservation_datetime).toISOString().slice(0, 16)" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="edit_table_number" class="text-sm font-semibold text-gray-700">Table Number</label>
                                <input type="text" name="table_number" id="edit_table_number" :value="reservations[editingReservation].table_number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="edit_room_number" class="text-sm font-semibold text-gray-700">Room Number</label>
                                <input type="text" name="room_number" id="edit_room_number" :value="reservations[editingReservation].room_number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
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

                            <div class="sm:col-span-2 flex flex-col gap-1.5">
                                <label for="edit_internal_notes" class="text-sm font-semibold text-gray-700">Internal Notes</label>
                                <textarea name="internal_notes" id="edit_internal_notes" rows="3" x-text="reservations[editingReservation].internal_notes" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 resize-none focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"></textarea>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="allergies_or_dietary" value="1" :checked="reservations[editingReservation].allergies_or_dietary" class="rounded border-gray-300 text-molveno-blue-500 focus:ring-molveno-blue-300">
                                    <span class="text-sm text-gray-700">Allergies or dietary requirements</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                        <button type="submit" class="inline-flex items-center justify-center bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white font-semibold shadow-sm px-4 py-2 text-sm rounded-lg transition-colors duration-150">
                            Save Changes
                        </button>
                        <button type="button" @click="showEditModal = false" class="inline-flex items-center justify-center bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 text-sm rounded-lg transition-colors duration-150">
                            Cancel
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
