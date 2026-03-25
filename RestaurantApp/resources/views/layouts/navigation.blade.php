<nav x-data="{ open: false }" class="bg-primary border-b border-molveno-blue-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @can('View Dishes')
                    <x-nav-link :href="route('dishes')" :active="request()->routeIs('dishes')">
                        {{ __('Dishes') }}
                    </x-nav-link>
                    @endcan
                    @can('View Statistics')
                    <x-nav-link :href="route('statistics')" :active="request()->routeIs('statistics')">
                        {{ __('Statistics') }}
                    </x-nav-link>
                    @endcan
                    @can('View Kitchen Orders')
                    <x-nav-link :href="route('kitchen-orders')" :active="request()->routeIs('kitchen-orders')">
                        {{ __('Kitchen Orders') }}
                    </x-nav-link>
                    @endcan
                    @can('View Orders')
                    <x-nav-link :href="route('ordermanagement')" :active="request()->routeIs('ordermanagement')">
                        {{ __('Order Management') }}
                    </x-nav-link>
                    @endcan
                    @can('View Table Management')
                    <x-nav-link :href="route('tablemanagement')" :active="request()->routeIs('tablemanagement')">
                        {{ __('Table Management') }}
                    </x-nav-link>
                    @endcan
                    @can('View Account Management')
                    <x-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')">
                        {{ __('Account Management') }}
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
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @can('View Dishes')
            <x-responsive-nav-link :href="route('dishes')" :active="request()->routeIs('dishes')">
                {{ __('Dishes') }}
            </x-responsive-nav-link>
            @endcan
            @can('View Statistics')
            <x-responsive-nav-link :href="route('statistics')" :active="request()->routeIs('statistics')">
                {{ __('Statistics') }}
            </x-responsive-nav-link>
            @endcan
            @can('View Kitchen Orders')
            <x-responsive-nav-link :href="route('kitchen-orders')" :active="request()->routeIs('kitchen-orders')">
                {{ __('Kitchen Orders') }}
            </x-responsive-nav-link>
            @endcan
            @can('View Orders')
            <x-responsive-nav-link :href="route('ordermanagement')" :active="request()->routeIs('ordermanagement')">
                {{ __('Order Management') }}
            </x-responsive-nav-link>
            @endcan
            @can('View Table Management')
            <x-responsive-nav-link :href="route('tablemanagement')" :active="request()->routeIs('tablemanagement')">
                {{ __('Table Management') }}
            </x-responsive-nav-link>
            @endcan
            @can('View Account Management')
            <x-responsive-nav-link :href="route('accounts.index')" :active="request()->routeIs('accounts.*')">
                {{ __('Account Management') }}
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
