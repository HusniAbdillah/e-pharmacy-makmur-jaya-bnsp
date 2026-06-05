<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Makmur Jaya E-Pharmacy') }}</title>

        <!-- Fonts: Using Inter as Saans alternative via Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-cream text-black selection:bg-orange selection:text-white">
        <div class="min-h-screen">
            <!-- Intercom-style Navigation -->
            <nav class="bg-white border-b border-oat sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <livewire:layout.navigation />
                </div>
            </nav>

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white border-b border-oat">
                    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
                        <h2 class="text-[32px] tracking-[-0.96px] leading-none">
                            {{ $header }}
                        </h2>
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>