@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'text-gray-800 border-gray-300 bg-white focus:border-molveno-blue-500 focus:ring-molveno-blue-500 rounded-md shadow-sm']) }}>
