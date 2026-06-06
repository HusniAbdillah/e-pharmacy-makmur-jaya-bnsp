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
            <!-- Navigation -->
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

        <!-- Toast Notification Container -->
        <div x-data="{ 
            toasts: [],
            add(toast) {
                toast.id = Date.now();
                this.toasts.push(toast);
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== toast.id);
                }, 5000);
            }
        }"
        @toast.window="add($event.detail)"
        class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full">
            <template x-for="toast in toasts" :key="toast.id">
                <div :class="{
                    'bg-semantic-danger text-white border-semantic-danger': toast.type === 'danger',
                    'bg-semantic-warning text-black border-semantic-warning': toast.type === 'warning',
                    'bg-semantic-success text-white border-semantic-success': toast.type === 'success',
                    'bg-black text-white border-black': toast.type === 'info' || !toast.type
                }" class="border p-4 rounded-lg shadow-lg flex items-start gap-3 transition duration-300">
                    <div class="flex-1">
                        <h4 class="font-semibold text-sm" x-text="toast.title"></h4>
                        <p class="text-xs mt-1 opacity-90" x-text="toast.message"></p>
                    </div>
                    <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="text-xs font-bold hover:opacity-75">
                        ✕
                    </button>
                </div>
            </template>
        </div>

        <!-- Listen for Livewire & API Toast triggers -->
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('toast-alert', (event) => {
                    let detail = event[0] || event;
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            title: detail.title || 'Notifikasi',
                            message: detail.message || '',
                            type: detail.type || 'info'
                        }
                    }));
                });

                @auth
                    @if(auth()->user()->isStaf())
                        // Fungsi cek stok kritis asinkron untuk Dashboard Staff
                        function cekStokKritis() {
                            fetch('/api/cek-stok-kritis')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.kritis && data.kritis.length > 0) {
                                        data.kritis.forEach(item => {
                                            window.dispatchEvent(new CustomEvent('toast', {
                                                detail: {
                                                    title: 'Stok Kritis!',
                                                    message: 'Stok obat ' + item.name + ' tersisa ' + item.stok + ' unit.',
                                                    type: 'danger'
                                                }
                                            }));
                                        });
                                    }
                                    if (data.hampir_kadaluarsa && data.hampir_kadaluarsa.length > 0) {
                                        data.hampir_kadaluarsa.forEach(item => {
                                            window.dispatchEvent(new CustomEvent('toast', {
                                                detail: {
                                                    title: 'Hampir Kadaluarsa!',
                                                    message: 'Batch ' + item.batch_number + ' obat ' + item.name + ' kadaluarsa pada ' + item.expiration_date,
                                                    type: 'warning'
                                                }
                                            }));
                                        });
                                    }
                                });
                        }
                        // Cek saat halaman dimuat pertama kali, lalu ulangi setiap 30 detik
                        setTimeout(cekStokKritis, 2000);
                        setInterval(cekStokKritis, 30000);
                    @endif
                @endauth
            });
        </script>
    </body>
</html>