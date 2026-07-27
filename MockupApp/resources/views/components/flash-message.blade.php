@props(['message'])

<div id="flash-msg" class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm font-medium px-4 py-3 rounded-xl shadow-sm">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-green-600">
        <path d="M20 6 9 17l-5-5"/>
    </svg>
    {{ $message }}
    <button onclick="document.getElementById('flash-msg').remove()" class="ml-auto text-green-500 hover:text-green-700">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
</div>