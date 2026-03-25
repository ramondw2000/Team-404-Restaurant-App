@props(['totalPending', 'totalReady', 'countCompleted'])

<div class="flex items-center gap-5 text-sm">
    <span class="flex items-center gap-1.5 text-gray-500">
        <x-ui.badge variant="custom" :dot="true" dotColor="bg-gray-300" class="bg-transparent text-gray-500 px-0">{{ $totalPending }} preparing</x-ui.badge>
    </span>
    <span class="flex items-center gap-1.5 text-amber-600 font-medium">
        <x-ui.badge variant="custom" :dot="true" dotColor="bg-amber-400" class="bg-transparent text-amber-600 px-0">{{ $totalReady }} ready</x-ui.badge>
    </span>
    <span class="flex items-center gap-1.5 text-green-600">
        <x-ui.badge variant="custom" :dot="true" dotColor="bg-green-400" class="bg-transparent text-green-600 px-0">{{ $countCompleted }} done</x-ui.badge>
    </span>
</div>
