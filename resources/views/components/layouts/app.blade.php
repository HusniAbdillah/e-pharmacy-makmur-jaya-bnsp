<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Makmur Jaya E-Pharmacy</title>
    {{-- Styles (Tailwind) --}}
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="bg-cream text-black font-sans antialiased selection:bg-orange selection:text-white">
    <div class="min-h-screen">
        {{-- Navigasi utama --}}
        <nav class="bg-white border-b border-oat sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <livewire:layout.navigation />
            </div>
        </nav>

        <main class="py-8">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    @vite(['resources/js/app.js'])
</body>
</html>
