<div
    x-data="{
        page: 1,
        showFilters: false,
    }"
    @set-page.window="page = $event.detail.page"
>
    <div class="bg-white border border-gray-100 rounded-xl shadow-sm">

        {{-- Header --}}
        <div class="px-6 pt-6 pb-4 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Order Ledger</p>
                    <h3 class="text-base font-semibold text-gray-900 mt-0.5">Completed orders</h3>
                </div>
                <div class="flex items-center gap-2">
                    @if(count($this->selectedOrders) > 0)
                        <x-ui.badge variant="primary">
                            {{ count($this->selectedOrders) }} selected
                        </x-ui.badge>
                        <x-ui.button variant="outline" size="sm" wire:click="batchPrint">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Batch Print
                        </x-ui.button>
                    @endif
                    <x-ui.button variant="outline" size="sm" wire:click="exportCsv">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export CSV
                    </x-ui.button>
                    <x-ui.badge variant="primary" class="bg-primary text-white">
                        {{ $this->filteredOrders->count() }} orders
                    </x-ui.badge>
                </div>
            </div>

            {{-- Search + Filter Toggle --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <x-ui.search-input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by order, customer, waiter, or location..."
                    />
                </div>
                <button
                    @click="showFilters = !showFilters"
                    :class="showFilters ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium border rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filters
                </button>
            </div>

            {{-- Filters Panel --}}
            <div
                x-show="showFilters"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="mt-4 p-4 bg-gray-50 rounded-xl space-y-4"
                x-cloak
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    {{-- Date Range --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Date Range</label>
                        <div class="flex flex-wrap gap-1">
                            @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'this_month' => 'This Month', 'last_month' => 'Last Month'] as $value => $label)
                                <button
                                    wire:click="setDateRange('{{ $value }}')"
                                    @class([
                                        'px-2.5 py-1 text-xs font-medium rounded-lg transition-colors',
                                        'bg-primary text-white' => $dateRange === $value,
                                        'bg-white text-gray-600 border border-gray-200 hover:bg-gray-100' => $dateRange !== $value,
                                    ])
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Payment Method</label>
                        <select
                            wire:model.live="paymentMethod"
                            class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary bg-white"
                        >
                            <option value="">All Methods</option>
                            @foreach($this->availablePaymentMethods as $method)
                                <option value="{{ $method }}">{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Location --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Location</label>
                        <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                            @foreach($this->availableLocations as $location)
                                <label class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-lg cursor-pointer transition-colors {{ in_array($location, $selectedLocations) ? 'bg-primary text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-100' }}">
                                    <input
                                        type="checkbox"
                                        value="{{ $location }}"
                                        wire:model.live="selectedLocations"
                                        class="sr-only"
                                    >
                                    {{ $location }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Waiter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Waiter</label>
                        <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                            @foreach($this->availableWaiters as $waiter)
                                <label class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-lg cursor-pointer transition-colors {{ in_array($waiter, $selectedWaiters) ? 'bg-primary text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-100' }}">
                                    <input
                                        type="checkbox"
                                        value="{{ $waiter }}"
                                        wire:model.live="selectedWaiters"
                                        class="sr-only"
                                    >
                                    {{ $waiter }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Order Type --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Order Type</label>
                    <div class="flex gap-1">
                        @foreach(['' => 'All', 'restaurant' => 'Dine-in', 'room_service' => 'Room Service'] as $value => $label)
                            <button
                                wire:click="$set('orderType', '{{ $value }}')"
                                @class([
                                    'px-3 py-1 text-xs font-medium rounded-lg transition-colors',
                                    'bg-primary text-white' => $orderType === $value,
                                    'bg-white text-gray-600 border border-gray-200 hover:bg-gray-100' => $orderType !== $value,
                                ])
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto" wire:loading.class="opacity-50">

            {{-- Loading Skeleton --}}
            <div wire:loading.flex wire:target="search,setDateRange,paymentMethod,selectedLocations,selectedWaiters,orderType" class="absolute inset-0 z-10 items-center justify-center bg-white/60">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Loading...
                </div>
            </div>

            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-white z-10">
                    <tr class="border-b border-slate-200">
                        <th class="pl-6 pr-2 pb-3 pt-4 w-10">
                            <input
                                type="checkbox"
                                wire:model.live="selectAllOnPage"
                                wire:change="toggleSelectAll"
                                class="rounded border-gray-300 text-primary focus:ring-primary/40"
                            >
                        </th>
                        <th wire:click="sortBy('id')" class="text-left pb-3 pt-4 text-xs font-semibold uppercase tracking-widest text-gray-400 cursor-pointer hover:text-gray-600 select-none">
                            <span class="inline-flex items-center gap-1">
                                Order
                                @if($sortField === 'id')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </span>
                        </th>
                        <th wire:click="sortBy('location')" class="text-left pb-3 pt-4 text-xs font-semibold uppercase tracking-widest text-gray-400 cursor-pointer hover:text-gray-600 select-none">
                            <span class="inline-flex items-center gap-1">
                                Location
                                @if($sortField === 'location')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </span>
                        </th>
                        <th wire:click="sortBy('waiter')" class="text-left pb-3 pt-4 text-xs font-semibold uppercase tracking-widest text-gray-400 cursor-pointer hover:text-gray-600 select-none">
                            <span class="inline-flex items-center gap-1">
                                Waiter
                                @if($sortField === 'waiter')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </span>
                        </th>
                        <th wire:click="sortBy('customer')" class="text-left pb-3 pt-4 text-xs font-semibold uppercase tracking-widest text-gray-400 cursor-pointer hover:text-gray-600 select-none">
                            <span class="inline-flex items-center gap-1">
                                Customer
                                @if($sortField === 'customer')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </span>
                        </th>
                        <th class="text-center pb-3 pt-4 text-xs font-semibold uppercase tracking-widest text-gray-400">Items</th>
                        <th wire:click="sortBy('total')" class="text-right pb-3 pt-4 text-xs font-semibold uppercase tracking-widest text-gray-400 cursor-pointer hover:text-gray-600 select-none">
                            <span class="inline-flex items-center gap-1 justify-end">
                                Total
                                @if($sortField === 'total')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </span>
                        </th>
                        <th wire:click="sortBy('completed_at')" class="text-right pb-3 pt-4 text-xs font-semibold uppercase tracking-widest text-gray-400 cursor-pointer hover:text-gray-600 select-none">
                            <span class="inline-flex items-center gap-1 justify-end">
                                Closed
                                @if($sortField === 'completed_at')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </span>
                        </th>
                        <th class="text-right pb-3 pt-4 pr-6 text-xs font-semibold uppercase tracking-widest text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->paginatedOrders as $order)
                        @php
                            $rowClass = $this->rowClasses($order);
                            $isSelected = in_array($order['id'], $selectedOrders);
                            $relativeTime = isset($order['completed_at_carbon']) ? $order['completed_at_carbon']->diffForHumans() : $order['closed_at'];
                        @endphp
                        <tr
                            wire:key="order-row-{{ $order['id'] }}"
                            @class([
                                'border-b border-slate-200 last:border-0 transition-colors',
                                $rowClass,
                                'bg-primary/5 ring-1 ring-inset ring-primary/20' => $isSelected && $rowClass === '',
                            ])
                        >
                            <td class="pl-6 pr-2 py-3">
                                <input
                                    type="checkbox"
                                    value="{{ $order['id'] }}"
                                    wire:model.live="selectedOrders"
                                    class="rounded border-gray-300 text-primary focus:ring-primary/40"
                                >
                            </td>
                            <td class="py-3 font-semibold text-gray-900">{{ $order['id'] }}</td>
                            <td class="py-3 text-gray-500">{{ $order['location'] }}</td>
                            <td class="py-3 text-gray-500">{{ $order['waiter'] }}</td>
                            <td class="py-3 text-gray-500">{{ $order['customer'] }}</td>
                            <td class="py-3 text-center text-gray-500">{{ count($order['items']) }} items</td>
                            <td class="py-3 text-right font-semibold text-molveno-blue-700">&euro; {{ number_format($order['total'], 2) }}</td>
                            <td class="py-3 text-right text-gray-400">{{ $relativeTime }}</td>
                            <td class="py-3 text-right pr-6">
                                <div class="inline-flex items-center gap-1">
                                    <button
                                        wire:click="viewReceipt('{{ $order['id'] }}')"
                                        class="p-1.5 text-gray-400 hover:text-primary rounded-lg hover:bg-gray-100 transition-colors"
                                        title="View Receipt"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button
                                        wire:click="printReceipt('{{ $order['id'] }}')"
                                        class="p-1.5 text-gray-400 hover:text-primary rounded-lg hover:bg-gray-100 transition-colors"
                                        title="Print Receipt"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-ui.empty-state title="No completed orders found" description="No completed orders found for the selected criteria.">
                                    <x-slot:icon>
                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </x-slot:icon>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-1">
                <span class="text-xs text-gray-500 mr-2">Rows per page:</span>
                @foreach([10, 25, 50] as $option)
                    <button
                        wire:click="setPerPage({{ $option }})"
                        @class([
                            'px-2.5 py-1 text-xs font-medium rounded-lg transition-colors',
                            'bg-primary text-white' => $perPage === $option,
                            'bg-gray-100 text-gray-600 hover:bg-gray-200' => $perPage !== $option,
                        ])
                    >
                        {{ $option }}
                    </button>
                @endforeach
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">
                    Page {{ $this->currentPage }} of {{ $this->totalPages }}
                </span>
                <div class="flex items-center gap-1">
                    <button
                        wire:click="previousPage"
                        @disabled($this->currentPage <= 1)
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button
                        wire:click="nextPage"
                        @disabled($this->currentPage >= $this->totalPages)
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Receipt Modal --}}
    @if($showReceiptModal && $this->receiptOrder)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-data
            @keydown.escape.window="$wire.closeReceipt()"
        >
            <div class="absolute inset-0 bg-black/40" wire:click="closeReceipt"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">

                {{-- Modal Header --}}
                <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
                    <h3 class="text-lg font-bold text-gray-900">Receipt</h3>
                    <button
                        wire:click="closeReceipt"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5" id="receipt-content-{{ $this->receiptOrder['id'] }}">

                    {{-- Order Header --}}
                    <div class="text-center pb-4 border-b border-dashed border-gray-200">
                        <p class="text-lg font-bold text-gray-900">{{ $this->receiptOrder['id'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $this->receiptOrder['completed_at'] ?? $this->receiptOrder['closed_at'] }}</p>
                        <div class="flex items-center justify-center gap-3 mt-2 text-xs text-gray-500">
                            <span>{{ $this->receiptOrder['location'] }}</span>
                            <span>&middot;</span>
                            <span>{{ $this->receiptOrder['waiter'] }}</span>
                        </div>
                        @if(!empty($this->receiptOrder['customer']))
                            <p class="text-xs text-gray-500 mt-1">Customer: {{ $this->receiptOrder['customer'] }}</p>
                        @endif
                    </div>

                    {{-- Itemized List --}}
                    <div>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-xs text-gray-400 uppercase tracking-wide">
                                    <th class="text-left pb-2">Item</th>
                                    <th class="text-center pb-2">Qty</th>
                                    <th class="text-right pb-2">Price</th>
                                    <th class="text-right pb-2">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->receiptOrder['items'] as $item)
                                    <tr class="border-b border-gray-50 last:border-0">
                                        <td class="py-2 text-gray-700">{{ $item['name'] }}</td>
                                        <td class="py-2 text-center text-gray-500">{{ $item['qty'] }}</td>
                                        <td class="py-2 text-right text-gray-500">&euro; {{ number_format($item['price'], 2) }}</td>
                                        <td class="py-2 text-right font-medium text-gray-700">&euro; {{ number_format($item['qty'] * $item['price'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary --}}
                    <div class="border-t border-dashed border-gray-200 pt-4 space-y-2">
                        @php
                            $taxPercent = (int) round(((float) config('tax.rate')) * 100);
                        @endphp
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span>
                            <span>&euro; {{ number_format($this->receiptOrder['subtotal'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Tax ({{ $taxPercent }}%)</span>
                            <span>&euro; {{ number_format($this->receiptOrder['tax'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-gray-900 pt-1 border-t border-gray-200">
                            <span>Total</span>
                            <span>&euro; {{ number_format($this->receiptOrder['total'], 2) }}</span>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="flex items-center justify-between text-sm bg-gray-50 rounded-lg px-4 py-2.5">
                        <span class="text-gray-500">Payment Method</span>
                        <span class="font-medium text-gray-700">{{ $this->receiptOrder['payment_method'] ?? 'N/A' }}</span>
                    </div>

                    {{-- Footer --}}
                    <div class="text-center pt-3 border-t border-dashed border-gray-200">
                        <p class="text-xs text-gray-400">Thank you for dining at Molveno Lake Resort</p>
                        <p class="text-xs text-gray-300 mt-1">We hope to see you again soon!</p>
                    </div>
                </div>

                {{-- Modal Actions --}}
                <div class="sticky bottom-0 bg-white border-t border-gray-100 px-6 py-4 flex justify-end gap-2 rounded-b-2xl">
                    <x-ui.button variant="secondary" size="sm" wire:click="closeReceipt">
                        Close
                    </x-ui.button>
                    <x-ui.button size="sm" wire:click="printReceipt('{{ $this->receiptOrder['id'] }}')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
