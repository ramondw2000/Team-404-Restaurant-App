@props(['order', 'allergenConfig'])

@php
$isRoom    = $order['type'] === 'room_service';
$headerBg  = $order['overall'] === 'completed' ? '#16a34a'
           : ($order['overall'] === 'ready'    ? '#d97706' : '#0084c4');
$overallClass = $order['overall'] === 'completed' ? 'text-green-600'
              : ($order['overall'] === 'ready'    ? 'text-amber-600' : 'text-gray-400');
@endphp

<div class="order-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col"
     data-overall="{{ $order['overall'] }}"
     data-type="{{ $order['type'] }}">

    <!-- ── Ticket header ──────────────────── -->
    <div class="px-4 py-3 text-white" style="background-color: {{ $headerBg }}">
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
                <p class="text-xs opacity-70 mt-0.5">{{ $order['waiter'] }}</p>
            </div>

            <!-- Right: time + summary -->
            <div class="text-right shrink-0">
                <p class="text-xs font-semibold">{{ $order['time'] }}</p>
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
            <x-orders.dish-row :dish="$dish" :allergenConfig="$allergenConfig" />
        @endforeach
    </div>

    <!-- ── Card footer ────────────────────── -->
    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
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

</div>
