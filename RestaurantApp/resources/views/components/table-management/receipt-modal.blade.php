@props([
    'show' => false,
    'receiptData' => null,
])

@if($show && $receiptData)
    <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" x-data>
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeReceipt"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm max-h-[90vh] flex flex-col" @click.stop>
            {{-- Header --}}
            <div class="flex items-center justify-between p-4 border-b border-gray-100 shrink-0">
                <h2 class="text-base font-bold text-gray-900">Receipt</h2>
                <button
                    wire:click="closeReceipt"
                    class="flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Receipt Content (printable) --}}
            <div class="flex-1 overflow-y-auto" id="receipt-content">
                <div class="p-6 space-y-5">
                    {{-- Restaurant Header --}}
                    <div class="text-center">
                        <h3 class="text-lg font-bold text-gray-900">Molveno Lake Resort</h3>
                        <p class="text-xs text-gray-500 mt-1">Restaurant & Bar</p>
                        <div class="mt-3 h-px bg-gray-200"></div>
                    </div>

                    {{-- Guest Info --}}
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Guest</span>
                            <span class="font-semibold text-gray-900">{{ $receiptData['guest_name'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Table</span>
                            <span class="text-gray-700">{{ $receiptData['table_name'] }}</span>
                        </div>
                        @if($receiptData['party_size'])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Party Size</span>
                                <span class="text-gray-700">{{ $receiptData['party_size'] }}</span>
                            </div>
                        @endif
                        @if($receiptData['reservation_time'])
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Reservation</span>
                                <span class="text-gray-700">{{ $receiptData['reservation_time'] }}</span>
                            </div>
                        @endif
                        @if($receiptData['order_count'] > 1)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Orders</span>
                                <span class="text-gray-700">{{ $receiptData['order_count'] }} orders combined</span>
                            </div>
                        @endif
                    </div>

                    <div class="h-px bg-gray-200"></div>

                    {{-- Items --}}
                    <div class="space-y-2">
                        @foreach($receiptData['items'] as $item)
                            <div class="flex justify-between text-sm">
                                <div class="min-w-0 flex-1">
                                    <span class="text-gray-900">{{ $item['quantity'] }}x</span>
                                    <span class="text-gray-700 ml-1">{{ $item['name'] }}</span>
                                    @if($item['quantity'] > 1)
                                        <span class="text-gray-400 text-xs ml-1">@ &euro;{{ number_format($item['unit_price'], 2) }}</span>
                                    @endif
                                </div>
                                <span class="font-medium text-gray-900 ml-3 shrink-0">&euro; {{ number_format($item['total'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="h-px bg-gray-200"></div>

                    {{-- Totals --}}
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span>&euro; {{ number_format($receiptData['subtotal'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tax ({{ (int) round(((float) config('tax.rate')) * 100) }}%)</span>
                            <span>&euro; {{ number_format($receiptData['tax'], 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-300">
                            <span>Total</span>
                            <span>&euro; {{ number_format($receiptData['total'], 2) }}</span>
                        </div>
                    </div>

                    <div class="h-px bg-gray-200"></div>

                    {{-- Footer --}}
                    <div class="text-center text-xs text-gray-400 space-y-1">
                        <p>Printed: {{ $receiptData['printed_at'] }}</p>
                        <p>Thank you for dining with us!</p>
                    </div>
                </div>
            </div>

            {{-- Print Button --}}
            <div class="p-4 border-t border-gray-100 shrink-0">
                <x-ui.button
                    @click="
                        const content = document.getElementById('receipt-content').innerHTML;
                        const w = window.open('', '_blank', 'width=400,height=600');
                        w.document.write('<html><head><title>Receipt</title><style>body{font-family:monospace;padding:20px;font-size:12px}*{margin:0;padding:0;box-sizing:border-box}.space-y-5>*+*{margin-top:1.25rem}.space-y-2>*+*{margin-top:0.5rem}.space-y-1\\.5>*+*{margin-top:0.375rem}.text-center{text-align:center}.flex{display:flex}.justify-between{justify-content:space-between}.font-bold{font-weight:700}.font-semibold{font-weight:600}.font-medium{font-weight:500}.text-lg{font-size:1.125rem}.text-base{font-size:1rem}.text-sm{font-size:0.875rem}.text-xs{font-size:0.75rem}.mt-1{margin-top:0.25rem}.mt-3{margin-top:0.75rem}.ml-1{margin-left:0.25rem}.ml-3{margin-left:0.75rem}.pt-2{padding-top:0.5rem}.p-6{padding:1.5rem}.min-w-0{min-width:0}.flex-1{flex:1}.shrink-0{flex-shrink:0}hr,.h-px{height:1px;background:#e5e7eb;border:none;margin:0.5rem 0}@media print{body{padding:0}}</style></head><body>' + content + '</body></html>');
                        w.document.close();
                        w.focus();
                        w.print();
                    "
                    class="w-full justify-center"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print Receipt
                </x-ui.button>
            </div>
        </div>
    </div>
@endif
