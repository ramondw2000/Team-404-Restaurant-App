@props(['label', 'active' => false])

<div class="relative h-full flex items-center" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <button @click="open = !open"
            class="inline-flex items-center gap-1 px-1 pt-1 border-b-2 text-sm font-medium leading-5 h-full focus:outline-none transition duration-150 ease-in-out
                {{ $active
                    ? 'border-white text-white'
                    : 'border-transparent text-white/70 hover:text-white hover:border-white/50 focus:text-white focus:border-white/50' }}">
        {{ $label }}
        <svg class="h-4 w-4 fill-current transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="absolute left-0 top-full z-50 mt-0 w-[32rem] rounded-lg border border-molveno-blue-700 bg-primary shadow-xl"
         style="display: none;"
         @click="open = false">
        <div class="grid grid-cols-2 gap-0.5 p-2">
            {{ $slot }}
        </div>
    </div>
</div>
