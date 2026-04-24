@props(['order'])

@php
$isBar        = $order['type'] === 'bar';
$overallClass = $order['overall'] === 'completed' ? 'text-green-600'
              : ($order['overall'] === 'ready'    ? 'text-amber-600' : 'text-gray-400');
$headerClass  = $order['overall'] === 'completed' ? 'bg-emerald-600'
              : ($order['overall'] === 'ready'    ? 'bg-amber-600' : 'bg-violet-700');
$canSend      = $order['cnt_pending'] === 0 && $order['cnt_ready'] > 0 && $order['overall'] !== 'completed';
$sendState    = $order['overall'] === 'completed' ? 'sent'
              : ($canSend ? 'ready' : 'disabled');
$sendClassMap = [
    'disabled' => 'bg-slate-50 border-slate-200 text-slate-400 cursor-not-allowed shadow-none',
    'ready'    => 'bg-gradient-to-r from-violet-400 to-purple-600 text-white border-transparent shadow-lg cursor-pointer',
    'sent'     => 'bg-emerald-600 border-emerald-600 text-white cursor-default shadow-md',
];
$cardStateClass = $order['overall'] === 'completed'
    ? 'bg-emerald-50 border-emerald-200 shadow-md'
    : 'bg-white border-gray-200 shadow-sm';
@endphp

<div class="order-card rounded-xl border overflow-hidden flex flex-col transition-colors duration-300 {{ $cardStateClass }}"
     data-overall="{{ $order['overall'] }}"
     data-type="{{ $order['type'] }}"
     data-order-id="{{ $order['id'] }}"
     data-order-db-id="{{ $order['id'] }}">

    <!-- ── Ticket header ──────────────────── -->
    <div class="px-4 py-3 text-white {{ $headerClass }}">
        <div class="flex items-start justify-between gap-2">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 text-xs font-bold bg-black/20 px-2 py-0.5 rounded">
                        @if($isBar)
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 3h16l-2 14H6Z"/><path d="M6 17h12"/><path d="M8 7v5"/></svg>
                            Bar
                        @else
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                            Table {{ $order['table'] }}
                        @endif
                    </span>
                    <span class="text-sm font-bold tracking-wide">{{ $order['id'] }}</span>
                </div>
                <p class="text-xs opacity-70 mt-0.5">{{ $order['waiter'] }}</p>
            </div>

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

    <!-- ── Drink list ──────────────────────── -->
    <div class="flex-1 divide-y divide-gray-100">
        @foreach($order['drinks'] as $drink)
            @php
            $dotClass = match($drink['status']) {
                'ready'  => 'bg-amber-400',
                'served' => 'bg-green-400',
                default  => 'bg-gray-300',
            };
            $markPendingClasses = 'border border-dashed border-slate-300 bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-700';
            $markReadyClasses   = 'bg-violet-600 border border-violet-700 text-white shadow-lg shadow-violet-200 hover:bg-violet-700';
            @endphp
            <div class="px-4 py-3 flex flex-col gap-2"
                 data-dish-wrapper
                 data-dish-status="{{ $drink['status'] }}">
                <div class="flex items-start gap-2">
                    <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $dotClass }}"
                          data-role="status-dot"
                          data-status="{{ $drink['status'] }}"
                          data-class-pending="bg-gray-300"
                          data-class-ready="bg-amber-400"
                          data-class-served="bg-green-400"></span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between gap-1">
                            <span class="text-sm font-semibold text-gray-800 leading-snug">{{ $drink['name'] }}</span>
                            <span class="text-xs text-gray-400 font-medium shrink-0">&times;{{ $drink['qty'] }}</span>
                        </div>
                        @if(!empty($drink['notes']))
                            <p class="text-xs text-gray-500 mt-0.5">{{ $drink['notes'] }}</p>
                        @endif
                    </div>
                </div>
                <div class="dish-action" data-order-id="{{ $order['id'] }}" data-dish-status="{{ $drink['status'] }}">
                    @if($drink['status'] === 'served')
                        <div class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 border border-green-200 px-2.5 py-1.5 rounded-lg">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                            Served
                        </div>
                    @else
                        <button type="button"
                                class="mark-ready-btn inline-flex items-center justify-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg w-full transition {{ $drink['status'] === 'ready' ? $markReadyClasses : $markPendingClasses }}"
                                data-role="mark-ready"
                                data-order-id="{{ $order['id'] }}"
                                data-dish-status="{{ $drink['status'] }}"
                                data-class-pending="{{ $markPendingClasses }}"
                                data-class-ready="{{ $markReadyClasses }}">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                            <span class="mark-ready-label">{{ $drink['status'] === 'ready' ? 'Ready' : 'Mark Ready' }}</span>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- ── Card footer ────────────────────── -->
    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-400">
                {{ $order['cnt_total'] }} {{ $order['cnt_total'] === 1 ? 'drink' : 'drinks' }}
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
                    class="order-send-btn inline-flex items-center justify-center gap-2 text-xs font-semibold px-3 py-2 rounded-lg border border-dashed transition focus:outline-none focus:ring-2 focus:ring-violet-200 {{ $sendClassMap[$sendState] }}"
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
