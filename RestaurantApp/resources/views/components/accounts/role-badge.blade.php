@props(['rc'])

<x-ui.badge variant="custom" :dot="true" :dotColor="$rc['dot']" class="{{ $rc['bg'] }} {{ $rc['text'] }}">
    {{ $rc['label'] }}
</x-ui.badge>
