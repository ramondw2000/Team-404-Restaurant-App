@props(['orderCount', 'countActive', 'countCompleted'])

<x-ui.tab-group>
    <x-ui.tab :active="true" :count="$orderCount" value="all" data-tab="all" data-default="true" onclick="barSwitchTab(this)">All</x-ui.tab>
    <x-ui.tab :active="false" :count="$countActive" value="active" data-tab="active" onclick="barSwitchTab(this)">Active</x-ui.tab>
    <x-ui.tab :active="false" :count="$countCompleted" value="completed" data-tab="completed" onclick="barSwitchTab(this)">Completed</x-ui.tab>
    <x-ui.divider orientation="vertical" class="h-5 mx-1 hidden sm:block" />
    <x-ui.tab :active="false" value="table" data-tab="table" onclick="barSwitchTab(this)">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
        </svg>
        Table Orders
    </x-ui.tab>
    <x-ui.tab :active="false" value="bar" data-tab="bar" onclick="barSwitchTab(this)">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8 2h8l-4 9v11"/><path d="M4 2h16"/><path d="M6 22h12"/>
        </svg>
        Bar Orders
    </x-ui.tab>
</x-ui.tab-group>
