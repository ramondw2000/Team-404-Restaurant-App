@props(['tables', 'serverName' => 'John Doe'])

<div class="flex items-center gap-3">
    <!-- Server pill -->
    <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 shadow-sm">
        <x-ui.avatar :name="$serverName" size="sm" />
        <span class="text-sm font-semibold text-gray-700">{{ $serverName }}</span>
    </div>

    <!-- Table picker -->
    <div class="relative">
        <select id="sel-table"
                class="appearance-none text-sm font-semibold border border-gray-200 rounded-lg pl-9 pr-8 py-2 bg-white text-gray-700 shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-molveno-blue-300 cursor-pointer">
            <option value="">Select table</option>
            @foreach($tables as $t)
                <option value="{{ $t }}">Table {{ $t }}</option>
            @endforeach
        </select>
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m6 9 6 6 6-6"/></svg>
    </div>
</div>
