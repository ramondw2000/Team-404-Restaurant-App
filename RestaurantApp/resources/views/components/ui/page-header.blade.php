@props([
    'title',
    'subtitle' => null,
    'helpPage' => null,
    'helpTitle' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3']) }}>
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-2xl font-black text-primary">{{ $title }}</h1>
            @if($helpPage)
                <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('open-sheet', { name: 'help-{{ $helpPage }}' })"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-primary hover:bg-gray-100 transition-colors"
                    title="How to use this page"
                    aria-label="Open help guide"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01"/>
                        <circle cx="12" cy="20" r="1" fill="currentColor"/>
                    </svg>
                </button>
            @endif
        </div>
        @if($subtitle)
            <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>

@if($helpPage)
    <x-help.sheet :page="$helpPage" :title="$helpTitle ?? 'How to use ' . $title" />
@endif
