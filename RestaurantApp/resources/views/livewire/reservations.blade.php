<div class="max-w-screen-2xl mx-auto w-full px-4 sm:px-6 py-8 flex flex-col gap-5 overflow-y-auto flex-1">

        {{-- Page Header --}}
        <x-ui.page-header
            :title="\Carbon\Carbon::parse($selectedDate)->translatedFormat('l, j F Y')"
            :subtitle="$this->totalCount . ' ' . ($this->totalCount === 1 ? 'reservation' : 'reservations') . ' this day'"
            help-page="reservations-list"
            help-title="How the Reservations list works"
        >
            <x-slot:actions>
                {{-- Calendar Popover --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button
                        @click="open = !open"
                        type="button"
                        class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium px-3 py-2 text-sm rounded-lg transition-colors duration-150 shadow-sm"
                        title="Pick a different day to view"
                    >
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</span>
                    </button>

                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 z-50 bg-white border border-gray-200 rounded-xl shadow-xl p-4 w-72"
                        x-cloak
                    >
                        <x-reservations.calendar-popover :selected-date="$selectedDate" />
                    </div>
                </div>

                <x-ui.button wire:click="openReservationModal" title="Create a new reservation for the selected day">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    New Reservation
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Search + Status Filters --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <x-ui.search-input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by guest name…"
                />
            </div>
            <div class="flex items-center gap-1.5 flex-wrap">
                @php
                    $filters = [
                        'all'       => 'All',
                        'scheduled' => 'Scheduled',
                        'arrived'   => 'Arrived',
                        'late'      => 'Late',
                        'departed'  => 'Departed',
                        'cancelled' => 'Cancelled',
                        'no_show'   => 'No Show',
                    ];
                @endphp
                @foreach($filters as $value => $label)
                    @php $count = $this->statusCounts[$value] ?? 0; @endphp
                    @if($value === 'all' || $count > 0)
                        <x-ui.tab
                            :active="$statusFilter === $value"
                            :count="$value !== 'all' ? $count : null"
                            wire:click="setStatusFilter('{{ $value }}')"
                        >
                            {{ $label }}
                        </x-ui.tab>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Reservation List --}}
        <x-ui.card padding="none">
            @if($this->groupedReservations->isEmpty())
                @php
                    $emptyDesc = $search ? 'No results for "' . $search . '".' : 'No reservations for this day.';
                @endphp
                <x-ui.empty-state
                    title="No reservations"
                    :description="$emptyDesc"
                    class="py-12 px-6"
                />
            @else
                <div class="divide-y divide-gray-100 px-2">
                    @foreach($this->groupedReservations as $timeSlot => $slotReservations)
                        @foreach($slotReservations as $index => $reservation)
                            @if($index === 0)
                                {{-- Time marker row --}}
                                <div class="flex items-center gap-3 py-2 px-1">
                                    <span class="text-[0.7rem] font-bold text-gray-400 tracking-widest uppercase w-10 shrink-0 text-right">{{ $timeSlot }}</span>
                                    <div class="flex-1 h-px bg-gray-100"></div>
                                </div>
                            @endif
                            <x-reservations.reservation-card :reservation="$reservation" />
                        @endforeach
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        {{-- Sheets --}}
        <x-reservations.detail-sheet :reservation="$selectedReservation" />
        <x-reservations.create-sheet
            :show-reservation-modal="$showReservationModal"
            :reservation-datetime="$reservationDatetime"
            :reservation-party-size="$reservationPartySize"
            :available-tables="$this->availableTables"
        />
        <x-reservations.edit-sheet />

        {{-- Toast --}}
        <x-ui.toast />
</div>
