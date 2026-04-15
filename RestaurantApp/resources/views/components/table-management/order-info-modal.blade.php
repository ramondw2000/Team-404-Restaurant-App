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
                    <h2 class="text-lg font-bold text-gray-900">{{ $orderInfo['order_number'] }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $orderInfo['table_name'] }}
                        @if($orderInfo['guest_name'])
                            &middot; {{ $orderInfo['guest_name'] }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $statusColor = match($orderInfo['status']) {
                            'active' => 'bg-blue-100 text-blue-800',
                            'draft' => 'bg-gray-100 text-gray-800',
                            'completed' => 'bg-green-100 text-green-800',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                        {{ ucfirst($orderInfo['status']) }}
                    </span>
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

            {{-- Order Items --}}
            <div class="flex-1 overflow-y-auto p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                    {{ $orderInfo['item_count'] }} items &middot; Ordered {{ $orderInfo['created_at'] }}
                </p>

                <div class="space-y-2">
                    @foreach($orderInfo['items'] as $item)
                        <div class="flex items-center justify-between py-2.5 px-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="shrink-0 w-7 h-7 rounded-full bg-molveno-blue-500 text-white text-xs font-bold flex items-center justify-center">
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

                {{-- Totals --}}
                <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span>&euro; {{ number_format($orderInfo['subtotal'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Tax (10%)</span>
                        <span>&euro; {{ number_format($orderInfo['tax'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-200">
                        <span>Total</span>
                        <span>&euro; {{ number_format($orderInfo['total'], 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-5 border-t border-gray-100 flex flex-col gap-2 shrink-0">
                <div class="flex gap-2">
                    <x-ui.button wire:click="openReceipt({{ $elementId }})" variant="secondary" class="flex-1 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print Receipt
                    </x-ui.button>
                    @if($orderInfo['status'] !== 'completed')
                        <x-ui.button wire:click="completeOrderForTable({{ $elementId }})" class="flex-1 justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Mark Finished
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
