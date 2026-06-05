import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                // Menggunakan Inter sebagai pengganti Saans untuk kemudahan integrasi
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'monospace'],
            },
            colors: {
                // Palet Utama Intercom
                cream: '#faf9f6',    // Background utama halaman
                black: '#111111',    // Teks utama dan tombol gelap
                white: '#ffffff',    // Background card/kontainer
                oat: '#dedbd6',      // Warna border wajib (hangat)
                orange: '#ff5600',   // Aksen utama (Tombol penting/CTA)
                sand: '#d3cec6',     // Warna netral terang
                
                // Skala Netral (Abu-abu hangat)
                gray: {
                    50: '#7b7b78',   // Teks muted/label form
                    60: '#626260',   // Teks sekunder
                    80: '#313130',   // Teks gelap alternatif
                },

                // Palet Report (Sangat berguna untuk Dasbor Admin)
                semantic: {
                    success: '#0bdf50', // Stok tersedia / Pesanan selesai
                    danger: '#c41c1c',  // Stok kritis / Kedaluwarsa / Error
                    warning: '#fe4c02', // Peringatan kedaluwarsa 30 hari
                    info: '#65b5ff',    // Status pesanan diproses
                    pink: '#ff2067',
                    lime: '#b3e01c',
                }
            },
            borderRadius: {
                'sm': '4px', // Wajib untuk semua Button dan Input Form
                'md': '6px', // Untuk elemen navigasi
                'lg': '8px', // Wajib untuk Card dan Modal
            },
            letterSpacing: {
                'tighter': '-0.04em', // Heading hero
                'tight': '-0.02em',   // Heading section
            },
            lineHeight: {
                'solid': '1.00',      // Khas Intercom untuk judul
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'), // Wajib untuk merapikan input form
    ],
};