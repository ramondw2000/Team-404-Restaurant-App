@props(['order', 'allergenConfig'])

@php
$isRoom       = $order['type'] === 'room_service';
$overallClass = $order['overall'] === 'completed' ? 'text-green-600'
              : ($order['overall'] === 'ready'    ? 'text-amber-600' : 'text-gray-400');
$headerClass  = $order['overall'] === 'completed' ? 'bg-emerald-600'
              : ($order['overall'] === 'ready'    ? 'bg-amber-600' : 'bg-sky-700');
$canSend      = $order['cnt_pending'] === 0 && $order['cnt_ready'] > 0 && $order['overall'] !== 'completed';
$sendState    = $order['overall'] === 'completed' ? 'sent'
              : ($canSend ? 'ready' : 'disabled');
$sendClassMap = [
    'disabled' => 'bg-slate-50 border-slate-200 text-slate-400 cursor-not-allowed shadow-none',
    'ready'    => 'bg-gradient-to-r from-sky-400 to-blue-600 text-white border-transparent shadow-lg cursor-pointer',
    'sent'     => 'bg-emerald-600 border-emerald-600 text-white cursor-default shadow-md',
];
@endphp

@php
$cardStateClass = $order['overall'] === 'completed'
    ? 'bg-emerald-50 border-emerald-200 shadow-md'
    : 'bg-white border-gray-200 shadow-sm';
@endphp

<div class="order-card rounded-xl border overflow-hidden flex flex-col transition-colors duration-300 {{ $cardStateClass }}"
     data-overall="{{ $order['overall'] }}"
     data-type="{{ $order['type'] }}"
     data-order-id="{{ $order['id'] }}"
     data-order-db-id="{{ $order['db_id'] }}">

    <!-- ── Ticket header ──────────────────── -->
    <div class="px-4 py-3 text-white {{ $headerClass }}">
        <div class="flex items-start justify-between gap-2">
            <!-- Left: location + order ID -->
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 text-xs font-bold bg-black/20 px-2 py-0.5 rounded">
                        @if($isRoom)
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4v16"/><path d="M22 8H2"/><path d="M22 20V8l-8-4H2"/></svg>
                            Room {{ $order['room'] }}
                        @else
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                            Table {{ $order['table'] }}
                        @endif
                    </span>
                    <span class="text-sm font-bold tracking-wide">{{ $order['id'] }}</span>
                </div>
                <p class="text-xs opacity-70 mt-0.5">
                    {{ $order['waiter'] }}
                    @if(!empty($order['customer']) && $order['customer'] !== '—')
                        &middot; {{ $order['customer'] }}
                    @endif
                </p>
            </div>

            <!-- Right: time + summary + delete -->
            <div class="text-right shrink-0">
                <div class="flex items-center justify-end gap-2">
                    <p class="text-xs font-semibold">{{ $order['time'] }}</p>
                    <button type="button"
                            class="delete-order-btn inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/20 hover:bg-red-500 transition-colors"
                            data-role="delete-order"
                            data-order-db-id="{{ $order['db_id'] }}"
                            title="Delete Order">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs opacity-70 mt-0.5">
                    @if($order['overall'] === 'completed')
                        All served
                    @else
                        @if($order['cnt_ready'] > 0)
                            {{ $order['cnt_ready'] }} ready &middot;
                        @endif
                        @if($order['cnt_pending'] > 0)
                            {{ $order['cnt_pending'] }} preparing
                        @endif
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- ── Dish list ──────────────────────── -->
    <div class="flex-1 divide-y divide-gray-100">
        @foreach($order['dishes'] as $dish)
            <x-orders.dish-row :dish="$dish" :allergenConfig="$allergenConfig" :orderId="$order['id']" :itemId="$dish['item_id']" :dishIndex="$loop->index" />
        @endforeach
    </div>

    <!-- ── Card footer ────────────────────── -->
    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-400">
                {{ $order['cnt_total'] }} {{ $order['cnt_total'] === 1 ? 'dish' : 'dishes' }}
                &middot; {{ $order['cnt_served'] }} served
            </span>
            <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $overallClass }}">
                @if($order['overall'] === 'completed')
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Completed
                @elseif($order['overall'] === 'ready')
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Ready to serve
                @else
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 1.5"/></svg>
                    Preparing
                @endif
            </span>
        </div>

        <div class="order-actions flex flex-col gap-2 {{ $order['overall'] === 'completed' ? 'hidden' : '' }}" data-role="order-actions">
            <button type="button"
                    class="order-send-btn inline-flex items-center justify-center gap-2 text-xs font-semibold px-3 py-2 rounded-lg border border-dashed transition focus:outline-none focus:ring-2 focus:ring-sky-200 {{ $sendClassMap[$sendState] }}"
                    data-role="send-order"
                    data-order-id="{{ $order['id'] }}"
                    data-send-state="{{ $sendState }}"
                    data-class-disabled="{{ $sendClassMap['disabled'] }}"
                    data-class-ready="{{ $sendClassMap['ready'] }}"
                    data-class-sent="{{ $sendClassMap['sent'] }}"
                    @if($sendState !== 'ready') disabled @endif>
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                <span class="order-send-label">{{ $order['overall'] === 'completed' ? 'Sent' : 'Send Out' }}</span>
            </button>
        </div>
    </div>

</div>
