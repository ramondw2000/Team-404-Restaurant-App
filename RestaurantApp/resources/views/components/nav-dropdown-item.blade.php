@props(['href', 'title', 'description' => '', 'active' => false])

<a href="{{ $href }}"
   class="block rounded-md px-3 py-2.5 transition duration-150 ease-in-out
       {{ $active
           ? 'bg-molveno-blue-700 text-white'
           : 'text-white/80 hover:bg-molveno-blue-700 hover:text-white' }}">
    <span class="block text-sm font-semibold">{{ $title }}</span>
    @if($description)
        <span class="block mt-0.5 text-xs text-white/60 leading-snug">{{ $description }}</span>
    @endif
</a>
