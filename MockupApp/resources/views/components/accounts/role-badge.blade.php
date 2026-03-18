@props(['rc'])

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $rc['bg'] }} {{ $rc['text'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $rc['dot'] }}"></span>
    {{ $rc['label'] }}
</span>
