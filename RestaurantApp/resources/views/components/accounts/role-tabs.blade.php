@props(['counts'])

<x-ui.tab-group>
    <x-ui.tab :active="true"  :count="$counts['all']"          value="all"          onclick="switchTab(this)" data-role="all">All</x-ui.tab>
    <x-ui.tab :active="false" :count="$counts['management']"   value="management"   onclick="switchTab(this)" data-role="management">Management</x-ui.tab>
    <x-ui.tab :active="false" :count="$counts['server']"       value="server"       onclick="switchTab(this)" data-role="server">Server</x-ui.tab>
    <x-ui.tab :active="false" :count="$counts['chef']"         value="chef"         onclick="switchTab(this)" data-role="chef">Chef</x-ui.tab>
    <x-ui.tab :active="false" :count="$counts['receptionist']" value="receptionist" onclick="switchTab(this)" data-role="receptionist">Receptionist</x-ui.tab>
    <x-ui.tab :active="false" :count="$counts['bar_staff']"    value="bar_staff"    onclick="switchTab(this)" data-role="bar_staff">Bar Staff</x-ui.tab>
    <x-ui.tab :active="false" :count="$counts['maintenance_crew']" value="maintenance_crew" onclick="switchTab(this)" data-role="maintenance_crew">Maintenance Crew</x-ui.tab>
</x-ui.tab-group>
