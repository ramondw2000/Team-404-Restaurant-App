{{-- Create Reservation Sheet --}}
<x-ui.sheet name="create-reservation" title="New Reservation" subtitle="Fill in the details below." maxWidth="md">
    <form id="create-reservation-form" method="POST" action="{{ route('reservations.store') }}" class="flex flex-col gap-5">
        @csrf

        <div class="flex flex-col gap-1.5">
            <label for="guest_name" class="text-sm font-semibold text-gray-700">Guest Name <span class="text-red-400">*</span></label>
            <input type="text" name="guest_name" id="guest_name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label for="phone" class="text-sm font-semibold text-gray-700">Phone</label>
                <input type="tel" name="phone" id="phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
                <input type="email" name="email" id="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label for="party_size" class="text-sm font-semibold text-gray-700">Number of Guests <span class="text-red-400">*</span></label>
                <input type="number" name="party_size" id="party_size" min="1" max="20" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="reservation_datetime" class="text-sm font-semibold text-gray-700">Date & Time <span class="text-red-400">*</span></label>
                <input type="datetime-local" name="reservation_datetime" id="reservation_datetime" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label for="table_number" class="text-sm font-semibold text-gray-700">Table Number</label>
                <input type="text" name="table_number" id="table_number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="room_number" class="text-sm font-semibold text-gray-700">Room Number</label>
                <input type="text" name="room_number" id="room_number" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
            </div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="internal_notes" class="text-sm font-semibold text-gray-700">Internal Notes</label>
            <textarea name="internal_notes" id="internal_notes" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 resize-none focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"></textarea>
        </div>
    </form>

    <x-slot:footer>
        <x-ui.button variant="secondary" x-on:click="$dispatch('close-sheet', 'create-reservation')">
            Cancel
        </x-ui.button>
        <x-ui.button type="submit" form="create-reservation-form">
            Save Reservation
        </x-ui.button>
    </x-slot:footer>
</x-ui.sheet>
