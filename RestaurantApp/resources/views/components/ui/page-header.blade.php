@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3']) }}>
    <div>
        <h1 class="text-2xl font-black text-primary">{{ $title }}</h1>
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
