@props(['counts', 'roles'])

<div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
    <x-ui.tab-group class="flex-nowrap min-w-max sm:flex-wrap sm:min-w-0">
        <x-ui.tab :active="true" :count="$counts['all']" value="all" onclick="switchTab(this)" data-role="all">All</x-ui.tab>
        @foreach($roles as $role)
            <x-ui.tab
                :active="false"
                :count="$counts[$role->name] ?? 0"
                :value="$role->name"
                onclick="switchTab(this)"
                :data-role="$role->name"
            >
                {{ ucwords(str_replace(['_', '-'], ' ', $role->name)) }}
            </x-ui.tab>
        @endforeach
    </x-ui.tab-group>
</div>
