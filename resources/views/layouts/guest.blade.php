<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Makmur Jaya E-Pharmacy') }}</title>

        <!-- Fonts: Inter via Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-cream text-black selection:bg-orange selection:text-white">

        <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

            {{-- Logo --}}
            <div class="mb-8">
                <a href="/" wire:navigate class="block">
                    <img src="{{ asset('e-pharmacy-logo.png') }}"
                         alt="Makmur Jaya E-Pharmacy"
                         class="h-10 w-auto mx-auto">
                </a>
            </div>

            {{-- Auth Card --}}
            <div class="w-full sm:max-w-md bg-white border border-oat rounded-lg shadow-sm p-8">
                {{ $slot }}
            </div>

        </div>

    </body>
</html>
