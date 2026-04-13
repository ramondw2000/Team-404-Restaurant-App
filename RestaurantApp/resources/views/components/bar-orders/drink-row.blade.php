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


</div>
