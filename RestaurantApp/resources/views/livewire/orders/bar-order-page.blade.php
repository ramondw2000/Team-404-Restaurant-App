<div
    class="min-h-screen bg-[#eaf4fa]"
    x-data="barOrderCart({{ json_encode($this->initialCart) }})"
    x-init="init()"
>
    <x-ordermanagement.styles />

    <div class="flex h-screen overflow-hidden">

        {{-- ── Main content ── --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            <div class="flex-1 overflow-y-auto pb-24">
                <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-5">

                    <x-ui.page-header
                        title="New Bar Order"
                        subtitle="Walk-up bar — no table required"
                    />

                    {{-- Search --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <x-ui.search-input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search drinks…"
                        />
                    </div>

                    {{-- Drink grid --}}
                    @if($this->dishes->isEmpty())
                        <x-ui.empty-state
                            icon="search"
                            title="No drinks available"
                            description="Mark dishes as 'Available at the bar' on the Dishes page to see them here."
                        />
                    @else
                        <x-ordermanagement.dish-grid>
                            @foreach($this->dishes as $dish)
                                @php($out = $dish->is_out_of_stock)
                                <div
                                    wire:key="bar-dish-{{ $dish->id }}"
                                    @class([
                                        'dish-card',
                                        'opacity-60 cursor-pointer' => $out,
                                    ])
                                    id="bar-dish-card-{{ $dish->id }}"
                                    @if($out)
                                        wire:click="$dispatch('open-dish-ingredients', { dishId: {{ $dish->id }} })"
                                    @endif
                                >
                                    <div class="dish-card-body">
                                        <div class="flex items-start gap-2 flex-wrap">
                                            <span class="text-sm font-bold text-gray-900 leading-snug">{{ $dish->name }}</span>
                                        </div>

                                        <p class="text-sm font-semibold text-primary">&euro;&nbsp;{{ number_format((float) $dish->price, 2) }}</p>

                                        @if($dish->description)
                                            <p class="text-xs text-gray-500 leading-snug">{{ $dish->description }}</p>
                                        @endif

                                        @if(!empty($dish->allergens))
                                            <div class="flex items-center gap-1 flex-wrap mt-1">
                                                @foreach($dish->allergens as $allergenKey)
                                                    @if(isset($this->allergenConfig[$allergenKey]))
                                                        <x-dishes.allergen-icon
                                                            :bg="$this->allergenConfig[$allergenKey]['bg']"
                                                            :icon="$this->allergenConfig[$allergenKey]['icon']"
                                                            :title="$this->allergenConfig[$allergenKey]['label']"
                                                            shadow />
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="dish-card-image">
                                        @if($dish->photo_path)
                                            <img src="{{ Storage::url($dish->photo_path) }}" alt="{{ $dish->name }}" @class(['grayscale' => $out])>
                                        @else
                                            <svg @class(['text-gray-300', 'grayscale' => $out]) width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round">
                                                <path d="M4 3h16l-2 14H6Z"/><path d="M6 17h12"/><path d="M8 7v5"/>
                                            </svg>
                                        @endif

                                        @if($out)
                                            <div class="absolute inset-0 bg-black/20 flex items-center justify-center pointer-events-none">
                                                <span class="px-2 py-1 bg-black/70 text-white text-[10px] font-semibold uppercase tracking-wide rounded">Out of stock</span>
                                            </div>
                                        @endif

                                        <div
                                            class="qty-badge"
                                            x-bind:class="(cart[{{ $dish->id }}]?.qty ?? 0) > 0 ? 'visible' : ''"
                                            x-text="cart[{{ $dish->id }}]?.qty ?? 0"
                                        ></div>

                                        @if(! $out)
                                            <button
                                                type="button"
                                                class="btn-add-dish"
                                                @click="openAddModal({{ $dish->id }}, {{ json_encode($dish->name) }}, {{ (float) $dish->price }})"
                                                aria-label="Add {{ $dish->name }}"
                                            >
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </x-ordermanagement.dish-grid>
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- Add-drink modal --}}
    <div
        x-show="addModal.open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
        x-cloak
        @click.self="closeAddModal()"
        @keydown.escape.window="closeAddModal()"
    >
        <div
            x-show="addModal.open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col"
            @click.stop
        >
            <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-base font-bold text-gray-900 leading-snug" x-text="addModal.name"></h2>
                    <p class="text-sm font-semibold text-molveno-blue-600 mt-0.5">&euro;&nbsp;<span x-text="addModal.price.toFixed(2)"></span></p>
                </div>
                <button @click="closeAddModal()" class="w-8 h-8 shrink-0 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" type="button">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-5 py-4 flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-gray-700">Quantity</span>
                    <div class="flex items-center gap-2 ms-auto">
                        <button type="button" class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors" @click="addModal.qty = Math.max(1, addModal.qty - 1)">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
                        </button>
                        <span class="text-sm font-bold text-gray-800 w-6 text-center" x-text="addModal.qty"></span>
                        <button type="button" class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors" @click="addModal.qty++">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Notes for the bar
                        <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea
                        x-model="addModal.notes"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-molveno-blue-400 focus:border-transparent"
                        rows="2"
                        placeholder="e.g. Extra ice, no straw…"
                    ></textarea>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
                <x-ui.button variant="secondary" class="flex-1 justify-center" x-on:click="closeAddModal()">Cancel</x-ui.button>
                <x-ui.button class="flex-1 justify-center" x-on:click="confirmAdd()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add to Order
                </x-ui.button>
            </div>
        </div>
    </div>

    {{-- Fixed order bar --}}
    <div
        x-show="itemCount > 0"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        class="fixed bottom-0 left-0 right-0 z-30 bg-white border-t border-gray-200 shadow-2xl px-4 sm:px-6 py-3"
        x-cloak
    >
        <div class="max-w-screen-xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-9 h-9 rounded-full bg-molveno-blue-500 flex items-center justify-center shrink-0 shadow-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 3h16l-2 14H6Z"/><path d="M6 17h12"/><path d="M8 7v5"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-800 truncate">
                        <span x-text="itemCount"></span> <span x-text="itemCount === 1 ? 'drink' : 'drinks'"></span>
                        <span class="text-gray-400 font-normal mx-1">&middot;</span>
                        <span class="text-gray-500 font-normal">Bar</span>
                    </p>
                    <p class="text-xs text-gray-400">&euro;&nbsp;<span x-text="orderTotal.toFixed(2)" class="font-semibold text-gray-600"></span></p>
                </div>
            </div>
            <x-ui.button class="shrink-0" x-on:click="openReview()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                Review Order
            </x-ui.button>
        </div>
    </div>

    {{-- Review screen --}}
    <div
        x-show="reviewOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-[#eaf4fa] flex flex-col"
        style="height: 100dvh;"
        x-cloak
    >
        <div class="shrink-0 bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-4">
                <x-ui.button variant="ghost" class="text-molveno-blue-500 hover:text-molveno-blue-700" x-on:click="reviewOpen = false">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    Back
                </x-ui.button>
                <h2 class="text-base font-bold text-gray-900 absolute left-1/2 -translate-x-1/2">Bar Order</h2>
                <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">Bar</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 flex flex-col gap-4">

                {{-- Guest Type Selector --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Payment Method</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                wire:model.live="guestType"
                                value="walk_in"
                                class="w-4 h-4 text-molveno-blue-500 border-gray-300 focus:ring-molveno-blue-400"
                            >
                            <span class="text-sm text-gray-700">Pay at Bar</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                wire:model.live="guestType"
                                value="hotel"
                                class="w-4 h-4 text-molveno-blue-500 border-gray-300 focus:ring-molveno-blue-400"
                            >
                            <span class="text-sm text-gray-700">Room Number</span>
                        </label>
                    </div>

                    {{-- Room Number Input (Hotel Guest only) --}}
                    <div x-show="$wire.guestType === 'hotel'" x-transition class="mt-3">
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Room Number</label>
                        <input
                            type="text"
                            wire:model.live="roomNumber"
                            placeholder="e.g. 101"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-molveno-blue-400 focus:border-transparent"
                        >
                    </div>
                </div>

                {{-- Walk-in Guest Actions --}}
                <div x-show="$wire.guestType === 'walk_in'" x-transition class="flex gap-3">
                    <x-ui.button
                        variant="secondary"
                        class="flex-1 justify-center"
                        wire:click="openTicket('print')"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2"/>
                            <path d="M9 17h6v4H9z"/>
                            <path d="M7 7h10v4H7z"/>
                        </svg>
                        Print Ticket
                    </x-ui.button>
                    <x-ui.button
                        variant="secondary"
                        class="flex-1 justify-center"
                        wire:click="openTicket('display')"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2"/>
                            <path d="M8 21h8"/>
                            <path d="M12 17v4"/>
                        </svg>
                        Display Ticket
                    </x-ui.button>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-molveno-blue-500 px-4 py-3 text-white">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold">Bar</span>
                            <span class="text-xs opacity-75" x-text="new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})"></span>
                        </div>
                    </div>

                    <template x-for="(item, dishId) in cart" :key="dishId">
                        <div class="px-4 py-3 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-3">
                                <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 bg-gray-300"></span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <span class="text-sm font-semibold text-gray-800" x-text="item.name"></span>
                                        <span class="text-xs text-gray-400 shrink-0">&times;<span x-text="item.qty"></span></span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="'€ ' + (item.price * item.qty).toFixed(2)"></p>
                                    <p x-show="item.notes" class="text-xs text-gray-400 italic mt-0.5" x-text="`&quot;` + item.notes + `&quot;`"></p>
                                </div>
                            </div>
                            <div class="mt-2 pl-5 flex items-center gap-2">
                                <button type="button" class="w-6 h-6 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors" @click="changeQty(dishId, -1)">
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                </button>
                                <span class="text-sm font-bold text-gray-700 w-5 text-center" x-text="item.qty"></span>
                                <button type="button" class="w-6 h-6 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors" @click="changeQty(dishId, 1)">
                                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                                <button type="button" class="ms-auto text-gray-300 hover:text-red-500 transition-colors" @click="removeItem(dishId)">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="px-4 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-400" x-text="Object.keys(cart).length + ' ' + (Object.keys(cart).length === 1 ? 'drink' : 'drinks')"></span>
                        <span class="text-xs font-semibold text-gray-600">&euro;&nbsp;<span x-text="orderTotal.toFixed(2)"></span></span>
                    </div>
                </div>

            </div>
        </div>

        <div class="shrink-0 bg-white border-t border-gray-200 shadow-[0_-4px_16px_rgba(0,0,0,.06)]">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500">Order total</p>
                    <p class="text-lg font-black text-gray-900 leading-tight">&euro;&nbsp;<span x-text="orderTotal.toFixed(2)"></span></p>
                </div>
                <x-ui.button
                    variant="secondary"
                    size="lg"
                    wire:click="markAsPaid"
                    class="shrink-0"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                    Mark as Paid
                </x-ui.button>
                <x-ui.button size="lg" class="shrink-0" x-on:click="submitOrder()" x-bind:disabled="submitting" x-bind:class="submitting ? 'opacity-50 cursor-not-allowed' : ''">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    <span x-text="submitting ? 'Placing…' : 'Place Order'"></span>
                </x-ui.button>
            </div>
        </div>
    </div>

    {{-- Ticket Modal (Print/Display) --}}
    @if($showTicketModal && $this->ticketData)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" x-data>
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeTicket"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm max-h-[90vh] flex flex-col" @click.stop>
                {{-- Header --}}
                <div class="flex items-center justify-between p-4 border-b border-gray-100 shrink-0">
                    <h2 class="text-base font-bold text-gray-900">
                        {{ $ticketMode === 'print' ? 'Print Ticket' : 'Order Ticket' }}
                    </h2>
                    <button
                        wire:click="closeTicket"
                        class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Ticket Content --}}
                <div class="flex-1 overflow-y-auto" id="ticket-content">
                    <div class="p-6 space-y-4">
                        {{-- Header --}}
                        <div class="text-center">
                            <h3 class="text-lg font-bold text-gray-900">Molveno Lake Resort</h3>
                            <p class="text-xs text-gray-500 mt-1">Bar Order</p>
                            <div class="mt-2 h-px bg-gray-200"></div>
                        </div>

                        {{-- Order Info --}}
                        <div class="space-y-1.5 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Order #</span>
                                <span class="font-medium text-gray-900">BAR-{{ str_pad($this->ticketData['order_id'], 3, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Time</span>
                                <span class="text-gray-700">{{ $this->ticketData['created_at'] ?? now()->format('H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Payment</span>
                                <span class="text-gray-700">{{ $this->ticketData['guest_type'] === 'hotel' ? 'Room Charge' : 'Pay at Bar' }}</span>
                            </div>
                            @if($this->ticketData['guest_type'] === 'hotel' && $this->ticketData['room_number'])
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Room</span>
                                    <span class="text-gray-700">{{ $this->ticketData['room_number'] }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-500">Server</span>
                                <span class="text-gray-700">{{ $this->ticketData['waiter'] }}</span>
                            </div>
                        </div>

                        <div class="h-px bg-gray-200"></div>

                        {{-- Items --}}
                        <div class="space-y-2">
                            @foreach($this->ticketData['items'] as $item)
                                <div class="flex justify-between text-sm">
                                    <div class="min-w-0 flex-1">
                                        <span class="text-gray-900">{{ $item['qty'] }}x</span>
                                        <span class="text-gray-700 ml-1">{{ $item['name'] }}</span>
                                        @if(!empty($item['notes']))
                                            <span class="text-gray-400 text-xs ml-1">({{ $item['notes'] }})</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="h-px bg-gray-200"></div>

                        {{-- Total --}}
                        <div class="flex justify-between text-base font-bold text-gray-900 pt-1">
                            <span>Total</span>
                            <span>&euro; {{ number_format($this->ticketData['total'], 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="p-4 border-t border-gray-100 shrink-0 flex gap-3">
                    @if($ticketMode === 'print')
                        <x-ui.button
                            @click="
                                const content = document.getElementById('ticket-content').innerHTML;
                                const w = window.open('', '_blank', 'width=400,height=600');
                                w.document.write('<html><head><title>Bar Ticket</title><style>body{font-family:system-ui,sans-serif;padding:20px;font-size:13px}*{margin:0;padding:0;box-sizing:border-box}.space-y-4>*+*{margin-top:1rem}.space-y-2>*+*{margin-top:0.5rem}.space-y-1\\.5>*+*{margin-top:0.375rem}.text-center{text-align:center}.flex{display:flex}.justify-between{justify-content:space-between}.font-bold{font-weight:700}.font-medium{font-weight:500}.text-lg{font-size:1.125rem}.text-base{font-size:1rem}.text-sm{font-size:0.875rem}.text-xs{font-size:0.75rem}.mt-1{margin-top:0.25rem}.mt-2{margin-top:0.5rem}.ml-1{margin-left:0.25rem}.pt-1{padding-top:0.25rem}.min-w-0{min-width:0}.flex-1{flex:1}.shrink-0{flex-shrink:0}hr,.h-px{height:1px;background:#e5e7eb;border:none;margin:0.5rem 0}@media print{body{padding:0}}</style></head><body>' + content + '</body></html>');
                                w.document.close();
                                w.focus();
                                w.print();
                            "
                            class="flex-1 justify-center"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print
                        </x-ui.button>
                    @endif
                    <x-ui.button
                        variant="secondary"
                        wire:click="closeTicket"
                        class="flex-1 justify-center"
                    >
                        Close
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif

    {{-- Ingredient-detail modal for out-of-stock dishes --}}
    <livewire:orders.dish-ingredients-modal />

    <script>
        function barOrderCart(initialCart = {}) {
            return {
                addModal: { open: false, dishId: null, name: '', price: 0, qty: 1, notes: '' },
                reviewOpen: false,
                submitting: false,
                orderNotes: '',
                cart: { ...initialCart },

                get itemCount() {
                    return Object.values(this.cart).reduce((sum, item) => sum + item.qty, 0);
                },

                get orderTotal() {
                    return Object.values(this.cart).reduce((sum, item) => sum + (item.price * item.qty), 0);
                },

                init() { this.cart = { ...initialCart }; },

                openAddModal(dishId, name, price) {
                    this.addModal = {
                        open: true,
                        dishId,
                        name,
                        price,
                        qty: 1,
                        notes: this.cart[dishId]?.notes ?? '',
                    };
                },

                closeAddModal() { this.addModal.open = false; },

                confirmAdd() {
                    const id = this.addModal.dishId;
                    if (this.cart[id]) {
                        this.cart[id].qty += this.addModal.qty;
                        if (this.addModal.notes.trim()) {
                            this.cart[id].notes = this.addModal.notes.trim();
                        }
                    } else {
                        this.cart[id] = {
                            name: this.addModal.name,
                            price: this.addModal.price,
                            qty: this.addModal.qty,
                            notes: this.addModal.notes.trim(),
                        };
                    }
                    this.closeAddModal();
                },

                changeQty(dishId, delta) {
                    if (!this.cart[dishId]) { return; }
                    const newQty = this.cart[dishId].qty + delta;
                    if (newQty < 1) {
                        this.removeItem(dishId);
                    } else {
                        this.cart[dishId].qty = newQty;
                    }
                },

                removeItem(dishId) {
                    delete this.cart[dishId];
                    this.cart = { ...this.cart };
                },

                openReview() {
                    if (this.itemCount === 0) { return; }
                    this.reviewOpen = true;
                },

                submitOrder() {
                    if (this.itemCount === 0 || this.submitting) { return; }
                    this.submitting = true;

                    const cartItems = Object.entries(this.cart).map(([dishId, item]) => ({
                        dish_id: parseInt(dishId),
                        qty: item.qty,
                        notes: item.notes,
                    }));

                    this.$wire.placeOrder(cartItems, this.orderNotes).then(() => {
                        this.submitting = false;
                    }).catch(() => {
                        this.submitting = false;
                    });
                },
            };
        }
    </script>
</div>
