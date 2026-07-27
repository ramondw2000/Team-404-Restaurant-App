{{-- Placeholder when no selection --}}
<div x-show="!selectedReservation" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <x-ui.empty-state title="Select a reservation" description="Click a reservation to view its details." />
</div>

{{-- Detail Panel when reservation selected --}}
<div x-show="selectedReservation" x-cloak class="space-y-4">
    <template x-if="selectedReservation && reservations[selectedReservation]">
        <div>
            {{-- Guest Info Card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-molveno-blue-500 text-sm font-bold text-white" x-text="reservations[selectedReservation].guest_name.substring(0, 2).toUpperCase()">
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900" x-text="reservations[selectedReservation].guest_name"></h3>
                        <p class="text-xs text-gray-500" x-text="new Date(reservations[selectedReservation].reservation_datetime).toLocaleString('en-US', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></p>
                    </div>
                </div>

                <div class="space-y-2.5 border-t border-gray-100 pt-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Guests</span>
                        <span class="text-sm font-semibold text-gray-900" x-text="reservations[selectedReservation].party_size + ' guests'"></span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Table</span>
                        <span x-show="reservations[selectedReservation].table_number" class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600" x-text="reservations[selectedReservation].table_number"></span>
                        <span x-show="!reservations[selectedReservation].table_number" class="text-sm text-gray-400">—</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="{
                                'bg-blue-100 text-blue-700': reservations[selectedReservation].status === 'scheduled',
                                'bg-green-100 text-green-700': reservations[selectedReservation].status === 'arrived',
                                'bg-gray-100 text-gray-600': reservations[selectedReservation].status === 'departed',
                                'bg-red-100 text-red-700': reservations[selectedReservation].status === 'cancelled',
                                'bg-amber-100 text-amber-700': reservations[selectedReservation].status === 'late',
                                'bg-purple-100 text-purple-700': reservations[selectedReservation].status === 'optional',
                                'bg-rose-100 text-rose-700': reservations[selectedReservation].status === 'no_show'
                            }"
                            x-text="reservations[selectedReservation].status.replace('_', ' ').charAt(0).toUpperCase() + reservations[selectedReservation].status.replace('_', ' ').slice(1)">
                        </span>
                    </div>
                </div>
            </div>

            {{-- Internal Notes --}}
            <div x-show="reservations[selectedReservation].internal_notes" class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4">
                <h4 class="mb-1.5 text-xs font-semibold text-blue-700">Internal Note</h4>
                <p class="text-sm text-blue-900" x-text="reservations[selectedReservation].internal_notes"></p>
            </div>

            {{-- Payment Info --}}
            <div x-show="reservations[selectedReservation].deposit_amount" class="mt-4 bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <h4 class="mb-2 text-xs font-semibold text-gray-500">Payment</h4>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Amount</span>
                        <span class="text-sm font-semibold text-gray-900" x-text="'€ ' + parseFloat(reservations[selectedReservation].deposit_amount).toFixed(2).replace('.', ',')"></span>
                    </div>
                    <div x-show="reservations[selectedReservation].deposit_status" class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="text-sm font-semibold text-gray-900" x-text="reservations[selectedReservation].deposit_status"></span>
                    </div>
                </div>
            </div>

            {{-- Contact Info --}}
            <div x-show="reservations[selectedReservation].phone || reservations[selectedReservation].email" class="mt-4 bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <h4 class="mb-2 text-xs font-semibold text-gray-500">Contact</h4>
                <div class="space-y-1.5">
                    <div x-show="reservations[selectedReservation].phone" class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Phone</span>
                        <span class="text-sm font-semibold text-gray-900" x-text="reservations[selectedReservation].phone"></span>
                    </div>
                    <div x-show="reservations[selectedReservation].email" class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Email</span>
                        <span class="text-sm font-semibold text-gray-900" x-text="reservations[selectedReservation].email"></span>
                    </div>
                    <div x-show="reservations[selectedReservation].room_number" class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Room</span>
                        <span class="text-sm font-semibold text-gray-900" x-text="reservations[selectedReservation].room_number"></span>
                    </div>
                </div>
            </div>

            {{-- Metadata --}}
            <div class="mt-4 bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <h4 class="mb-2 text-xs font-semibold text-gray-500">Metadata</h4>
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Created</span>
                        <span class="text-sm font-semibold text-gray-900" x-text="reservations[selectedReservation].created_at ? new Date(reservations[selectedReservation].created_at).toLocaleDateString('en-US', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'"></span>
                    </div>
                </div>
            </div>

            {{-- Status Action Buttons --}}
            <div class="mt-4 space-y-2">
                <div class="flex flex-wrap gap-2">
                    <template x-for="action in [
                        { status: 'arrived', label: 'Arrived' },
                        { status: 'departed', label: 'Departed' }
                    ]">
                        <form method="POST" :action="`/reservations/${selectedReservation}/status`" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" :value="action.status">
                            <button type="submit"
                                x-show="reservations[selectedReservation].status !== action.status"
                                class="w-full bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 text-sm rounded-lg transition-colors duration-150"
                                x-text="action.label">
                            </button>
                        </form>
                    </template>
                </div>
                <div class="flex flex-wrap gap-2">
                    <template x-for="action in [
                        { status: 'late', label: 'Late' },
                        { status: 'cancelled', label: 'Cancelled' }
                    ]">
                        <form method="POST" :action="`/reservations/${selectedReservation}/status`" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" :value="action.status">
                            <button type="submit"
                                x-show="reservations[selectedReservation].status !== action.status"
                                class="w-full bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 text-sm rounded-lg transition-colors duration-150"
                                x-text="action.label">
                            </button>
                        </form>
                    </template>
                </div>
                <form method="POST" :action="`/reservations/${selectedReservation}/status`">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="no_show">
                    <button type="submit"
                        x-show="reservations[selectedReservation].status !== 'no_show'"
                        class="w-full bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 text-sm rounded-lg transition-colors duration-150">
                        No Show
                    </button>
                </form>
            </div>

            {{-- Edit Button --}}
            <div class="mt-4">
                <button @click="editingReservation = selectedReservation; $dispatch('open-sheet', 'edit-reservation')" class="w-full inline-flex items-center justify-center bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white font-semibold shadow-sm px-4 py-2 text-sm rounded-lg transition-colors duration-150">
                    Edit Reservation
                </button>
            </div>
        </div>
    </template>
</div>
