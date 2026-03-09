<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-white antialiased bg-red-400">
        <div class="min-h-screen flex flex-col sm:gap-4 items-center bg-white sm:pb-6">
            @include('layouts.guest-navigation')

            <div class="flex flex-col md:flex-row items-stretch m-auto w-full max-w-4xl border-gray-200 sm:border sm:shadow  sm:shadow-gray-600">
                <div class="flex flex-col justify-center p-8 gap-4 bg-gray-200">
                    <img class="max-w-full border-gray-800 border-2 px-8 bg-white rounded-lg mx-auto" src="{{ asset('images/molveno-logo.png') }}" alt="molveno lake resort logo"/>
                    <h1 class="text-3xl text-molveno-blue-300 font-black text-center">Molveno Lake Resort</h1>
                    <span class="text-xl text-white text-wrap font-bold bg-molveno-blue-300 p-4 rounded-lg tracking-wide uppercase text-center">Restaurant</span>
                </div>

                <div class="flex flex-col justify-center overflow-hidden w-full sm:bg-molveno-blue-300 sm:px-8">
                    <div class="bg-molveno-blue-300 px-4 sm:px-0 py-8 rounded-lg w-full">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
