<div class="w-full bg-molveno-blue-500">
    <div class="flex items-center justify-between p-4">
        <a class="flex gap-4 items-center" href="/">
            <img class="max-w-16 max-h-16 bg-white rounded-lg border border-black" src="{{ asset('images/molveno-logo.png') }}" alt="molveno lake resort logo"/>
            <span class="text-wrap text-2xl">Molveno Lake Resort<br/><span class="text-molveno-blue-700 bg-white px-2 py-px rounded-r-lg rounded-bl-lg font-medium">Restaurant</span></span>
        </a>

        @auth
        <nav class="hidden sm:flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('dashboard') ? 'bg-molveno-blue-700' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('dishes') }}" class="px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('dishes') ? 'bg-molveno-blue-700' : '' }}">
                Dishes
            </a>
            <a href="{{ route('statistics') }}" class="px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('statistics') ? 'bg-molveno-blue-700' : '' }}">
                Statistics
            </a>
            <a href="{{ route('orders') }}" class="px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('orders') ? 'bg-molveno-blue-700' : '' }}">
                Orders
            </a>
            <a href="{{ route('ordermanagement') }}" class="px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('ordermanagement') ? 'bg-molveno-blue-700' : '' }}">
                Order Management
            </a>
            <a href="{{ route('tablemanagement') }}" class="px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('tablemanagement') ? 'bg-molveno-blue-700' : '' }}">
                Table Management
            </a>
        </nav>
        @endauth
    </div>

    @auth
    <!-- Mobile nav -->
    <div class="sm:hidden border-t border-molveno-blue-700 px-4 py-2 flex flex-wrap gap-2">
        <a href="{{ route('dashboard') }}" class="px-3 py-1 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('dashboard') ? 'bg-molveno-blue-700' : '' }}">
            Dashboard
        </a>
        <a href="{{ route('dishes') }}" class="px-3 py-1 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('dishes') ? 'bg-molveno-blue-700' : '' }}">
            Dishes
        </a>
        <a href="{{ route('statistics') }}" class="px-3 py-1 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('statistics') ? 'bg-molveno-blue-700' : '' }}">
            Statistics
        </a>
        <a href="{{ route('orders') }}" class="px-3 py-1 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('orders') ? 'bg-molveno-blue-700' : '' }}">
            Orders
        </a>
        <a href="{{ route('ordermanagement') }}" class="px-3 py-1 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('ordermanagement') ? 'bg-molveno-blue-700' : '' }}">
            Order Management
        </a>
        <a href="{{ route('tablemanagement') }}" class="px-3 py-1 rounded-md text-sm font-medium text-white hover:bg-molveno-blue-700 {{ request()->routeIs('tablemanagement') ? 'bg-molveno-blue-700' : '' }}">
            Table Management
        </a>
    </div>
    @endauth
</div>
