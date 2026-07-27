@props([
    'title' => 'No results found',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 text-center']) }}>
    @if(isset($icon))
        {{ $icon }}
    @else
        <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
    @endif

    <p class="text-sm font-semibold text-gray-500">{{ $title }}</p>

    @if($description)
        <p class="text-sm text-gray-400 mt-1">{{ $description }}</p>
    @endif

    @if(isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
