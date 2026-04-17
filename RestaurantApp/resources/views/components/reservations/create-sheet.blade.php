@props([
    'availableTables',
    'createFloorPlanElementId',
    'createDatetime'  => '',
    'createPartySize' => 2,
])

<x-ui.sheet name="create-reservation" title="New Reservation" subtitle="Fill in the details below." maxWidth="md">
    <div class="flex flex-col gap-4">

        <div class="flex flex-col gap-1.5">
            <label for="create_guest_name" class="text-sm font-semibold text-gray-700">Guest Name <span class="text-red-400">*</span></label>
            <input
                type="text"
                id="create_guest_name"
                wire:model="createGuestName"
                autocomplete="off"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300 @error('createGuestName') border-red-400 @enderror"
            >
            @error('createGuestName')
                <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label for="create_phone" class="text-sm font-semibold text-gray-700">Phone</label>
                <input
                    type="tel"
                    id="create_phone"
                    wire:model="createPhone"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"
                >
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="create_email" class="text-sm font-semibold text-gray-700">Email</label>
                <input
                    type="email"
                    id="create_email"
                    wire:model="createEmail"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"
                >
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
                <label for="create_party_size" class="text-sm font-semibold text-gray-700">Guests <span class="text-red-400">*</span></label>
                <input
                    type="number"
                    id="create_party_size"
                    wire:model.live="createPartySize"
                    min="1"
                    max="20"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300 @error('createPartySize') border-red-400 @enderror"
                >
                @error('createPartySize')
                    <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="create_room_number" class="text-sm font-semibold text-gray-700">Room</label>
                <input
                    type="text"
                    id="create_room_number"
                    wire:model="createRoomNumber"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"
                >
            </div>
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="create_datetime" class="text-sm font-semibold text-gray-700">Date & Time <span class="text-red-400">*</span></label>
            <input
                type="datetime-local"
                id="create_datetime"
                wire:model.live="createDatetime"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300 @error('createDatetime') border-red-400 @enderror"
            >
            @error('createDatetime')
                <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Table picker --}}
        <div class="flex flex-col gap-1.5">
            <label for="create_table" class="text-sm font-semibold text-gray-700">Table</label>

            @if($createDatetime && $createPartySize >= 1)
                @if($availableTables->isEmpty())
                    <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        No available tables for this time and party size.
                    </p>
                @else
                    <select
                        id="create_table"
                        wire:model="createFloorPlanElementId"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"
                    >
                        <option value="">No table preference</option>
                        @foreach($availableTables as $table)
                            <option value="{{ $table->id }}">{{ $table->table_name }} — {{ $table->seat_count }} seats</option>
                        @endforeach
                    </select>
                @endif
            @else
                <div class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-400 bg-gray-50 cursor-not-allowed">
                    Fill in guests and time first
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="create_notes" class="text-sm font-semibold text-gray-700">Internal Notes</label>
            <textarea
                id="create_notes"
                wire:model="createNotes"
                rows="3"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white outline-none transition-[border-color,box-shadow] duration-150 resize-none focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300"
            ></textarea>
        </div>
    </div>

    <x-slot:footer>
        <x-ui.button wire:click="createReservation">Save Reservation</x-ui.button>
    </x-slot:footer>
</x-ui.sheet>
