<x-app-layout>
    {{-- Welcome banner --}}
    <div class="bg-gradient-to-br from-primary to-molveno-blue-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <p class="text-molveno-blue-100 text-sm font-medium uppercase tracking-widest mb-1">Molveno Lake Resort</p>
            <h1 class="text-3xl font-bold text-white">Welcome back, {{ Auth::user()->name }}</h1>
            <p class="text-white/60 mt-1 text-sm">Select a section to get started.</p>
        </div>
    </div>

    {{-- Navigation grid --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            {{-- Kitchen Orders --}}
            <a href="{{ route('kitchen-orders') }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex items-start gap-4 hover:border-molveno-blue-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-lg bg-molveno-blue-500/10 flex items-center justify-center text-molveno-blue-500 group-hover:bg-molveno-blue-500 group-hover:text-white transition-colors duration-200">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                        <line x1="6" y1="17" x2="18" y2="17"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 group-hover:text-molveno-blue-700 transition-colors duration-200">Kitchen Orders</h3>
                    <p class="text-sm text-gray-500 mt-1 leading-relaxed">Monitor and track live orders from the kitchen queue.</p>
                </div>
                <svg class="shrink-0 w-4 h-4 text-gray-300 group-hover:text-molveno-blue-500 mt-1 transition-colors duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </a>

            {{-- Order Management --}}
            <a href="{{ route('ordermanagement') }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex items-start gap-4 hover:border-molveno-blue-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-lg bg-molveno-blue-500/10 flex items-center justify-center text-molveno-blue-500 group-hover:bg-molveno-blue-500 group-hover:text-white transition-colors duration-200">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1"/>
                        <line x1="9" y1="12" x2="15" y2="12"/>
                        <line x1="9" y1="16" x2="13" y2="16"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 group-hover:text-molveno-blue-700 transition-colors duration-200">Order Management</h3>
                    <p class="text-sm text-gray-500 mt-1 leading-relaxed">Create and manage orders for tables and room service.</p>
                </div>
                <svg class="shrink-0 w-4 h-4 text-gray-300 group-hover:text-molveno-blue-500 mt-1 transition-colors duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </a>

            {{-- Dishes --}}
            <a href="{{ route('dishes') }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex items-start gap-4 hover:border-molveno-blue-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-lg bg-molveno-blue-500/10 flex items-center justify-center text-molveno-blue-500 group-hover:bg-molveno-blue-500 group-hover:text-white transition-colors duration-200">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/>
                        <path d="M7 2v20"/>
                        <path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 group-hover:text-molveno-blue-700 transition-colors duration-200">Dishes</h3>
                    <p class="text-sm text-gray-500 mt-1 leading-relaxed">Browse and manage the restaurant's menu and dishes.</p>
                </div>
                <svg class="shrink-0 w-4 h-4 text-gray-300 group-hover:text-molveno-blue-500 mt-1 transition-colors duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </a>

            {{-- Account Management --}}
            <a href="{{ route('accounts.index') }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex items-start gap-4 hover:border-molveno-blue-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-lg bg-molveno-blue-500/10 flex items-center justify-center text-molveno-blue-500 group-hover:bg-molveno-blue-500 group-hover:text-white transition-colors duration-200">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 group-hover:text-molveno-blue-700 transition-colors duration-200">Account Management</h3>
                    <p class="text-sm text-gray-500 mt-1 leading-relaxed">Manage staff accounts, roles, and permissions.</p>
                </div>
                <svg class="shrink-0 w-4 h-4 text-gray-300 group-hover:text-molveno-blue-500 mt-1 transition-colors duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </a>

            {{-- Table Management --}}
            <a href="{{ route('tablemanagement') }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex items-start gap-4 hover:border-molveno-blue-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-lg bg-molveno-blue-500/10 flex items-center justify-center text-molveno-blue-500 group-hover:bg-molveno-blue-500 group-hover:text-white transition-colors duration-200">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 group-hover:text-molveno-blue-700 transition-colors duration-200">Table Management</h3>
                    <p class="text-sm text-gray-500 mt-1 leading-relaxed">Oversee table layouts, availability, and assignments.</p>
                </div>
                <svg class="shrink-0 w-4 h-4 text-gray-300 group-hover:text-molveno-blue-500 mt-1 transition-colors duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </a>

            {{-- Statistics --}}
            <a href="{{ route('statistics') }}"
               class="group bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex items-start gap-4 hover:border-molveno-blue-500 hover:shadow-md transition-all duration-200">
                <div class="shrink-0 w-11 h-11 rounded-lg bg-molveno-blue-500/10 flex items-center justify-center text-molveno-blue-500 group-hover:bg-molveno-blue-500 group-hover:text-white transition-colors duration-200">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="9"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/>
                        <line x1="2" y1="20" x2="22" y2="20"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 group-hover:text-molveno-blue-700 transition-colors duration-200">Statistics</h3>
                    <p class="text-sm text-gray-500 mt-1 leading-relaxed">View sales reports, trends, and performance insights.</p>
                </div>
                <svg class="shrink-0 w-4 h-4 text-gray-300 group-hover:text-molveno-blue-500 mt-1 transition-colors duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </a>

        </div>
    </div>
</x-app-layout>
