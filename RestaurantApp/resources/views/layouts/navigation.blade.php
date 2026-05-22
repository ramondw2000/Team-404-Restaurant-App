<nav x-data="{ open: false }" data-impersonating="{{ session('impersonation.original_user_id') ? '1' : '0' }}" class="bg-primary border-b border-molveno-blue-700">
<script>
    window.addEventListener('pageshow', function () {
        var nav = document.querySelector('nav[data-impersonating]');
        if (!nav) return;
        var rendered = nav.getAttribute('data-impersonating');
        fetch('{{ route('impersonation.status') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (data) { if (data.active !== (rendered === '1')) window.location.reload(); })
            .catch(function () {});
    });
</script>
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="block rounded-lg" style="background-color: rgb(234, 244, 250);">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex items-stretch">

                    {{-- Dashboard --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- Floor Operations --}}
                    @canany(['View Orders', 'View Table Management', 'View Reservations'])
                    <x-nav-dropdown label="Floor Operations" :active="request()->routeIs('tablemanagement') || request()->routeIs('reservations.*') || request()->routeIs('combined-orders.*')">
                        @can('View Table Management')
                        <x-nav-dropdown-item
                            :href="route('tablemanagement')"
                            title="Table Management"
                            description="Real-time floor plan, seating, and table status."
                            :active="request()->routeIs('tablemanagement')" />
                        @endcan
                        @can('Create Order')
                        <x-nav-dropdown-item
                            :href="route('combined-orders.new')"
                            title="New Order (Food & Drinks)"
                            description="Place a combined order for both food and drinks."
                            :active="request()->routeIs('combined-orders.*')" />
                        @endcan
                        @can('View Reservations')
                        <x-nav-dropdown-item
                            :href="route('reservations.index')"
                            title="Reservations"
                            description="Handle future bookings and guest lists."
                            :active="request()->routeIs('reservations.*')" />
                        @endcan
                    </x-nav-dropdown>
                    @endcanany

                    {{-- Production Units --}}
                    @canany(['View Kitchen Orders', 'View Bar Orders', 'View Dishes', 'Create Bar Order'])
                    <x-nav-dropdown label="Production Units" :active="request()->routeIs('orders') || request()->routeIs('dishes') || request()->routeIs('bar-orders.*')">
                        @canany(['View Kitchen Orders', 'View Bar Orders'])
                        <x-nav-dropdown-item
                            :href="route('orders')"
                            title="Orders"
                            description="Combined Kitchen and Bar orders with toggle."
                            :active="request()->routeIs('orders')" />
                        @endcanany
                        @can('Create Bar Order')
                        <x-nav-dropdown-item
                            :href="route('bar-orders.create')"
                            title="New Bar Order"
                            description="Walk-up bar order — no table required."
                            :active="request()->routeIs('bar-orders.*')" />
                        @endcan
                        @can('View Dishes')
                        <x-nav-dropdown-item
                            :href="route('dishes')"
                            title="Dishes"
                            description="Manage menu items, ingredients, and pricing."
                            :active="request()->routeIs('dishes')" />
                        @endcan
                    </x-nav-dropdown>
                    @endcanany

                    {{-- Statistics --}}
                    @can('View Statistics')
                    <x-nav-link :href="route('statistics')" :active="request()->routeIs('statistics')">
                        {{ __('Statistics') }}
                    </x-nav-link>
                    @endcan

                    {{-- Maintenance --}}
                    @can('View Maintenance')
                    <x-nav-link :href="route('maintenance')" :active="request()->routeIs('maintenance')">
                        {{ __('Maintenance') }}
                    </x-nav-link>
                    @endcan

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white/80 bg-transparent hover:text-white focus:outline-none transition ease-in-out duration-150">
                            @if(session('impersonation.original_user_id'))
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-semibold bg-amber-400 text-amber-900">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    Impersonating
                                </span>
                            @endif
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if(session('impersonation.original_user_id'))
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="flex items-center gap-1.5 text-amber-600 font-semibold text-xs mb-1">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    Impersonating
                                </div>
                                <div class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::user()->getRoleNames()->join(', ') ?: 'No role' }}</div>
                            </div>
                            <div class="py-1">
                                <form method="POST" action="{{ route('impersonation.stop') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm font-medium text-amber-600 hover:bg-amber-50 hover:text-amber-700 transition-colors">
                                        Stop Impersonating
                                    </button>
                                </form>
                            </div>
                            <div class="border-t border-gray-100"></div>
                        @endif

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @can('View Account Management')
                        <x-dropdown-link :href="route('accounts.index')">
                            {{ __('Accounts') }}
                        </x-dropdown-link>
                        @endcan

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                             onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white/70 hover:text-white hover:bg-molveno-blue-700 focus:outline-none focus:bg-molveno-blue-700 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">

            {{-- Dashboard --}}
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            {{-- Floor Operations --}}
            @canany(['View Orders', 'View Table Management', 'View Reservations'])
            <div class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-white/40">Floor Operations</div>
            @can('View Table Management')
            <x-responsive-nav-link :href="route('tablemanagement')" :active="request()->routeIs('tablemanagement')">
                {{ __('Table Management') }}
            </x-responsive-nav-link>
            @endcan
            @can('View Reservations')
            <x-responsive-nav-link :href="route('reservations.index')" :active="request()->routeIs('reservations.*')">
                {{ __('Reservations') }}
            </x-responsive-nav-link>
            @endcan
            @endcanany

            {{-- Production Units --}}
            @canany(['View Kitchen Orders', 'View Bar Orders', 'View Dishes', 'Create Bar Order'])
            <div class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-white/40">Production Units</div>
            @canany(['View Kitchen Orders', 'View Bar Orders'])
            <x-responsive-nav-link :href="route('orders')" :active="request()->routeIs('orders')">
                {{ __('Orders') }}
            </x-responsive-nav-link>
            @endcanany
            @can('Create Bar Order')
            <x-responsive-nav-link :href="route('bar-orders.create')" :active="request()->routeIs('bar-orders.*')">
                {{ __('New Bar Order') }}
            </x-responsive-nav-link>
            @endcan
            @can('View Dishes')
            <x-responsive-nav-link :href="route('dishes')" :active="request()->routeIs('dishes')">
                {{ __('Dishes') }}
            </x-responsive-nav-link>
            @endcan
            @endcanany

            {{-- Statistics --}}
            @can('View Statistics')
            <x-responsive-nav-link :href="route('statistics')" :active="request()->routeIs('statistics')">
                {{ __('Statistics') }}
            </x-responsive-nav-link>
            @endcan

            {{-- Maintenance --}}
            @can('View Maintenance')
            <x-responsive-nav-link :href="route('maintenance')" :active="request()->routeIs('maintenance')">
                {{ __('Maintenance') }}
            </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-white/20">
            <div class="px-4">
                @if(session('impersonation.original_user_id'))
                    <div class="flex items-center gap-1.5 text-amber-300 text-xs font-semibold mb-1">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Impersonating
                    </div>
                @endif
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-white/60">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                @if(session('impersonation.original_user_id'))
                    <form method="POST" action="{{ route('impersonation.stop') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-left block ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-amber-300 hover:text-amber-200 hover:bg-molveno-blue-700 hover:border-amber-400 focus:outline-none transition duration-150 ease-in-out">
                            Stop Impersonating
                        </button>
                    </form>
                @endif

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @can('View Account Management')
                <x-responsive-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')">
                    {{ __('Accounts') }}
                </x-responsive-nav-link>
                @endcan

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                                           onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
