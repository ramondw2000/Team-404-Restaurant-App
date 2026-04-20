@props(['drink'])

@php
$dotClassMap = [
    'pending' => 'bg-gray-300',
    'ready'   => 'bg-amber-400',
    'served'  => 'bg-green-400',
];
$dotClass = $dotClassMap[$drink['status']] ?? $dotClassMap['pending'];
@endphp

<div class="px-4 py-3 flex flex-col gap-2" data-drink-wrapper data-drink-status="{{ $drink['status'] }}">

    <!-- Drink name row -->
    <div class="flex items-start gap-2">
        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $dotClass }}"
              data-role="status-dot"
              data-status="{{ $drink['status'] }}"
              data-class-pending="{{ $dotClassMap['pending'] }}"
              data-class-ready="{{ $dotClassMap['ready'] }}"
              data-class-served="{{ $dotClassMap['served'] }}"></span>

        <div class="flex-1 min-w-0">
            <div class="flex items-baseline justify-between gap-1">
                <span class="text-sm font-semibold text-gray-800 leading-snug">{{ $drink['name'] }}</span>
                <span class="text-xs text-gray-600 font-medium shrink-0">&times;{{ $drink['qty'] }}</span>
            </div>
        </div>
    </div>

    <!-- Notes (if any) -->
    @if(!empty($drink['notes']))
        <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-md px-2 py-1.5">{{ $drink['notes'] }}</p>
    @endif

    <!-- Action buttons -->
    <div class="drink-status" data-drink-status="{{ $drink['status'] }}">
        @if($drink['status'] === 'served')
            <div class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 border border-green-200 px-2.5 py-1.5 rounded-lg">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                Served
            </div>
        @else
            <button type="button"
                    class="mark-ready-btn inline-flex items-center justify-center gap-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg w-full transition {{ $drink['status'] === 'ready' ? 'bg-sky-600 border border-sky-700 text-white shadow-lg shadow-sky-200 hover:bg-sky-700' : 'border border-dashed border-slate-300 bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}"
                    data-role="mark-ready"
                    data-drink-status="{{ $drink['status'] }}"
                    data-class-pending="border border-dashed border-slate-300 bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                    data-class-ready="bg-sky-600 border border-sky-700 text-white shadow-lg shadow-sky-200 hover:bg-sky-700">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                <span class="mark-ready-label">{{ $drink['status'] === 'ready' ? 'Ready' : 'Mark Ready' }}</span>
            </button>
        @endif
    </div>

</div>
