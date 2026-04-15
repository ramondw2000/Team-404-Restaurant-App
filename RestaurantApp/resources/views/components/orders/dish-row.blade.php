@props(['dish', 'allergenConfig', 'orderId', 'dishIndex', 'itemId'])

@php
$dotClassMap = [
    'pending' => 'bg-gray-300',
    'ready'   => 'bg-amber-400',
    'served'  => 'bg-green-400',
];
$dotClass = $dotClassMap[$dish['status']] ?? $dotClassMap['pending'];
$noteClasses = 'w-full bg-slate-50 border border-slate-200 rounded-md px-2 py-1.5 text-xs text-slate-700 focus:ring-2 focus:ring-sky-200 focus:border-sky-400 placeholder:text-slate-400 transition';
$markPendingClasses = 'border border-dashed border-slate-300 bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-700';
$markReadyClasses   = 'bg-sky-600 border border-sky-700 text-white shadow-lg shadow-sky-200 hover:bg-sky-700';
@endphp

<div class="px-4 py-3 flex flex-col gap-2" data-dish-wrapper data-dish-status="{{ $dish['status'] }}">

    <!-- Dish name row -->
    <div class="flex items-start gap-2">
        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $dotClass }}"
              data-role="status-dot"
              data-status="{{ $dish['status'] }}"
              data-class-pending="{{ $dotClassMap['pending'] }}"
              data-class-ready="{{ $dotClassMap['ready'] }}"
              data-class-served="{{ $dotClassMap['served'] }}"></span>

        <div class="flex-1 min-w-0">
            <div class="flex items-baseline justify-between gap-1">
                <span class="text-sm font-semibold text-gray-800 leading-snug">{{ $dish['name'] }}</span>
                <span class="text-xs text-gray-400 font-medium shrink-0">&times;{{ $dish['qty'] }}</span>
            </div>
        </div>
    </div>

    <!-- Notes textarea -->
    <textarea class="{{ $noteClasses }}" rows="2"
              placeholder="No notes…">{{ $dish['notes'] }}</textarea>

    <!-- Action buttons -->
    <div class="dish-action" data-order-id="{{ $orderId }}" data-item-id="{{ $itemId }}" data-dish-status="{{ $dish['status'] }}" data-dish-index="{{ $dishIndex }}">
        @if($dish['status'] === 'served')
            <div class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 border border-green-200 px-2.5 py-1.5 rounded-lg">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                Served
            </div>
        @else
            <button type="button"
                    class="mark-ready-btn inline-flex items-center justify-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg w-full transition {{ $dish['status'] === 'ready' ? $markReadyClasses : $markPendingClasses }}"
                    data-role="mark-ready"
                    data-order-id="{{ $orderId }}"
                    data-dish-status="{{ $dish['status'] }}"
                    data-class-pending="{{ $markPendingClasses }}"
                    data-class-ready="{{ $markReadyClasses }}">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                <span class="mark-ready-label">{{ $dish['status'] === 'ready' ? 'Ready' : 'Mark Ready' }}</span>
            </button>
        @endif
    </div>

</div>
