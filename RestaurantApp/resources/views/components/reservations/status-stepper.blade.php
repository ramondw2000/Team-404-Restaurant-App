@props(['status', 'reservationId'])

@php
    $steps = [
        ['key' => 'scheduled', 'label' => 'Scheduled'],
        ['key' => 'arrived',   'label' => 'Arrived'],
        ['key' => 'departed',  'label' => 'Departed'],
    ];

    $stepIndex = match ($status) {
        'scheduled' => 0,
        'arrived'   => 1,
        'departed'  => 2,
        default     => -1,  // off-flow status
    };

    $isTerminal = in_array($status, ['departed', 'cancelled', 'no_show']);
    $isOffFlow  = $stepIndex === -1;
@endphp

{{-- Main stepper --}}
<div class="flex items-center gap-0">
    @foreach($steps as $i => $step)
        @php
            $isDone    = $i < $stepIndex;
            $isCurrent = $i === $stepIndex;
            $isNext    = $i === $stepIndex + 1;
            $isFirst   = $i === 0;
            $isLast    = $i === count($steps) - 1;
        @endphp

        {{-- Connector before (not for first) --}}
        @if(! $isFirst)
            <div class="flex-1 h-px {{ $isDone || $isCurrent ? 'bg-molveno-blue-400' : 'bg-gray-200' }} transition-colors duration-300"></div>
        @endif

        {{-- Step node --}}
        <div class="flex flex-col items-center gap-1.5 shrink-0">
            @if($isNext && ! $isTerminal && ! $isOffFlow)
                {{-- Clickable next step --}}
                <button
                    type="button"
                    wire:click="advanceStatus({{ $reservationId }})"
                    class="w-8 h-8 rounded-full border-2 border-molveno-blue-400 bg-white text-molveno-blue-500 hover:bg-molveno-blue-50 hover:border-molveno-blue-600 flex items-center justify-center transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-molveno-blue-300"
                    title="Mark as {{ $step['label'] }}"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            @elseif($isDone || $isCurrent)
                {{-- Completed / active step --}}
                <div class="w-8 h-8 rounded-full {{ $isCurrent ? 'bg-molveno-blue-500 ring-4 ring-molveno-blue-100' : 'bg-molveno-blue-500' }} flex items-center justify-center transition-all duration-300">
                    @if($isDone)
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    @else
                        <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
                    @endif
                </div>
            @else
                {{-- Future / disabled step --}}
                <div class="w-8 h-8 rounded-full border-2 border-gray-200 bg-white flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                </div>
            @endif

            <span class="text-[0.65rem] font-semibold uppercase tracking-wide {{ $isCurrent ? 'text-molveno-blue-600' : ($isDone ? 'text-gray-500' : 'text-gray-300') }}">
                {{ $step['label'] }}
            </span>
        </div>

        {{-- Connector after (not for last) --}}
        @if(! $isLast)
            <div class="flex-1 h-px {{ $isDone ? 'bg-molveno-blue-400' : 'bg-gray-200' }} transition-colors duration-300"></div>
        @endif
    @endforeach
</div>

{{-- Destructive / off-flow actions --}}
@if(! $isTerminal)
    <div class="mt-4 flex items-center gap-2 justify-center flex-wrap">
        <span class="text-xs text-gray-400 font-medium">Mark as:</span>

        @if($status !== 'late')
            <button
                type="button"
                wire:click="setStatus({{ $reservationId }}, 'late')"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg border border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors duration-150"
            >
                Late
            </button>
        @endif

        @if($status !== 'no_show')
            <button
                type="button"
                wire:click="setStatus({{ $reservationId }}, 'no_show')"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg border border-rose-200 text-rose-700 bg-rose-50 hover:bg-rose-100 transition-colors duration-150"
            >
                No Show
            </button>
        @endif

        @if($status !== 'cancelled')
            <button
                type="button"
                wire:click="setStatus({{ $reservationId }}, 'cancelled')"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100 transition-colors duration-150"
            >
                Cancel
            </button>
        @endif
    </div>
@endif
