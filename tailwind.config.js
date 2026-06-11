import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Warna dasar aplikasi — nilai aslinya CSS variable yang
                // di-inject dari pengaturan admin (partials/theme.blade.php),
                // sehingga bisa diganti tanpa build ulang. Default: navy #1E3A8A.
                unm: {
                    50: 'rgb(var(--unm-50) / <alpha-value>)',
                    100: 'rgb(var(--unm-100) / <alpha-value>)',
                    200: 'rgb(var(--unm-200) / <alpha-value>)',
                    300: 'rgb(var(--unm-300) / <alpha-value>)',
                    400: 'rgb(var(--unm-400) / <alpha-value>)',
                    500: 'rgb(var(--unm-500) / <alpha-value>)',
                    600: 'rgb(var(--unm-600) / <alpha-value>)',
                    700: 'rgb(var(--unm-700) / <alpha-value>)',
                    800: 'rgb(var(--unm-800) / <alpha-value>)',
                    900: 'rgb(var(--unm-900) / <alpha-value>)',
                },
            },
        },
    },

    plugins: [forms],
};
