@props(['orderCount', 'countActive', 'countCompleted'])

<x-ui.tab-group>
    <x-ui.tab :active="true" :count="$orderCount" value="all" data-tab="all" data-default="true" onclick="switchTab(this)">All</x-ui.tab>
    <x-ui.tab :active="false" :count="$countActive" value="active" data-tab="active" onclick="switchTab(this)">Active</x-ui.tab>
    <x-ui.tab :active="false" :count="$countCompleted" value="completed" data-tab="completed" onclick="switchTab(this)">Completed</x-ui.tab>
    <x-ui.divider orientation="vertical" class="h-5 mx-1 hidden sm:block" />
    <x-ui.tab :active="false" value="restaurant" data-tab="restaurant" onclick="switchTab(this)">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
        </svg>
        Restaurant
    </x-ui.tab>
    <x-ui.tab :active="false" value="room_service" data-tab="room_service" onclick="switchTab(this)">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2 4v16"/><path d="M22 8H2"/><path d="M22 20V8l-8-4H2"/>
        </svg>
        Room Service
    </x-ui.tab>
</x-ui.tab-group>
