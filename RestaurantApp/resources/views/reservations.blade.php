<x-app-layout>
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5" x-data="{
        selectedReservation: null,
        reservations: @js($dinnerReservations->flatten(1)->keyBy('id')->toArray()),
        editingReservation: null
    }">
        <!-- Page Header -->
        <x-ui.page-header title="Reservations" subtitle="Molveno Lake Resort — Restaurant">
            <x-slot:actions>
                <form method="GET" action="{{ route('reservations.index') }}" class="flex items-center gap-2">
                    <input type="date" id="date" name="date" value="{{ $selectedDate }}"
                        onchange="this.form.submit()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300">
                </form>
                <x-ui.button @click="$dispatch('open-sheet', 'create-reservation')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    New Reservation
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <!-- Content Grid -->
        <div class="grid gap-5 lg:grid-cols-3">
            <!-- Reservations List -->
            <div class="lg:col-span-2">
                <x-ui.card>
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Dinner</h3>
                            <p class="text-xs text-gray-500 mt-0.5">17:00 – 23:00</p>
                        </div>
                        <x-ui.badge variant="primary">
                            {{ $dinnerReservations->flatten()->count() }} reservations
                        </x-ui.badge>
                    </div>

                    <div class="space-y-5">
                        @forelse($dinnerReservations as $timeSlot => $slotReservations)
                            @php
                                $slotTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $timeSlot);
                            @endphp
                            <div>
                                <div class="mb-2 flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-molveno-blue-500 text-[0.65rem] font-bold text-white">
                                        {{ $slotTime->format('H:i') }}
                                    </span>
                                </div>

                                <div class="space-y-1.5">
                                    @foreach($slotReservations as $reservation)
                                        <x-reservations.reservation-card :reservation="$reservation" />
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state title="No dinner reservations" description="There are no reservations for this date." />
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            <!-- Detail Panel -->
            <div class="lg:col-span-1">
                <x-reservations.detail-panel />
            </div>
        </div>

        <x-reservations.create-modal />
        <x-reservations.edit-modal />
    </div>
</x-app-layout>
