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
                // Oranye UNM (#F77F00) + turunannya
                unm: {
                    50: '#FFF6EA',
                    100: '#FFE7C7',
                    200: '#FFD095',
                    300: '#FFB45C',
                    400: '#FF9A2E',
                    500: '#F77F00',
                    600: '#DD7200',
                    700: '#B85F00',
                    800: '#8F4A00',
                    900: '#6B3700',
                },
            },
        },
    },

    plugins: [forms],
};
