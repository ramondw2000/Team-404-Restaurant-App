@props([
    'show' => false,
    'orderInfo' => null,
    'elementId' => null,
])

@if($show && $orderInfo)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeOrderInfo"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col" @click.stop>
            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-100 shrink-0">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Order Overview</h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $orderInfo['table_name'] }}
                        @if($orderInfo['guest_name'])
                            &middot; {{ $orderInfo['guest_name'] }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($orderInfo['unpaid_order_count'] > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            {{ $orderInfo['unpaid_order_count'] }} unpaid
                        </span>
                    @endif
                    @if($orderInfo['paid_order_count'] > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            {{ $orderInfo['paid_order_count'] }} paid
                        </span>
                    @endif
                    <button
                        wire:click="closeOrderInfo"
                        class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    {{ $orderInfo['order_count'] }} {{ \Illuminate\Support\Str::plural('order', $orderInfo['order_count']) }}
                    @if($orderInfo['order_count'] > 1)
                        &middot; {{ $orderInfo['first_order_at'] }} → {{ $orderInfo['latest_order_at'] }}
                    @else
                        &middot; {{ $orderInfo['first_order_at'] }}
                    @endif
                </p>

                {{-- Unpaid Section --}}
                @if($orderInfo['unpaid_order_count'] > 0)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-bold uppercase tracking-wide text-amber-700">Unpaid</h3>
                            <span class="text-xs font-semibold text-amber-700">&euro; {{ number_format($orderInfo['unpaid']['total'], 2) }}</span>
                        </div>
                        <div class="space-y-2">
                            @foreach($orderInfo['unpaid']['items'] as $item)
                                <div class="flex items-center justify-between py-2.5 px-3 bg-amber-50 ring-1 ring-amber-100 rounded-xl">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="shrink-0 w-7 h-7 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center">
                                            {{ $item['quantity'] }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</p>
                                            @if($item['notes'])
                                                <p class="text-xs text-gray-400 truncate">{{ $item['notes'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 ml-3">
                                        <p class="text-sm font-semibold text-gray-900">&euro; {{ number_format($item['total'], 2) }}</p>
                                        @if($item['quantity'] > 1)
                                            <p class="text-xs text-gray-400">&euro; {{ number_format($item['unit_price'], 2) }} each</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Paid Section --}}
                @if($orderInfo['paid_order_count'] > 0)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-bold uppercase tracking-wide text-green-700">Paid</h3>
                            <span class="text-xs font-semibold text-green-700">&euro; {{ number_format($orderInfo['paid']['total'], 2) }}</span>
                        </div>
                        <div class="space-y-2">
                            @foreach($orderInfo['paid']['items'] as $item)
                                <div class="flex items-center justify-between py-2.5 px-3 bg-gray-50 rounded-xl opacity-80">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="shrink-0 w-7 h-7 rounded-full bg-green-500 text-white text-xs font-bold flex items-center justify-center">
                                            {{ $item['quantity'] }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-700 truncate">{{ $item['name'] }}</p>
                                            @if($item['notes'])
                                                <p class="text-xs text-gray-400 truncate">{{ $item['notes'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 ml-3">
                                        <p class="text-sm font-semibold text-gray-700">&euro; {{ number_format($item['total'], 2) }}</p>
                                        @if($item['quantity'] > 1)
                                            <p class="text-xs text-gray-400">&euro; {{ number_format($item['unit_price'], 2) }} each</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Totals --}}
                <div class="pt-4 border-t border-gray-200 space-y-2">
                    @if($orderInfo['unpaid_order_count'] > 0 && $orderInfo['paid_order_count'] > 0)
                        <div class="flex justify-between text-sm text-amber-700">
                            <span>Unpaid total</span>
                            <span>&euro; {{ number_format($orderInfo['unpaid']['total'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-green-700">
                            <span>Paid total</span>
                            <span>&euro; {{ number_format($orderInfo['paid']['total'], 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span>&euro; {{ number_format($orderInfo['grand_subtotal'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Tax ({{ (int) round(((float) config('tax.rate')) * 100) }}%)</span>
                        <span>&euro; {{ number_format($orderInfo['grand_tax'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-200">
                        <span>Grand Total</span>
                        <span>&euro; {{ number_format($orderInfo['grand_total'], 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-5 border-t border-gray-100 flex flex-col gap-2 shrink-0">
                <div class="flex gap-2">
                    @if($orderInfo['unpaid_order_count'] > 0)
                        <x-ui.button wire:click="openReceipt({{ $elementId }})" variant="secondary" class="flex-1 justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print Receipt
                        </x-ui.button>
                        <x-ui.button wire:click="completeOrderForTable({{ $elementId }})" class="flex-1 justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            @if($orderInfo['unpaid_order_count'] > 1)
                                Mark All Paid
                            @else
                                Mark Paid
                            @endif
                        </x-ui.button>
                    @else
                        <div class="flex-1 text-center text-xs text-green-700 bg-green-50 rounded-xl py-3 font-semibold">
                            All orders settled
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
