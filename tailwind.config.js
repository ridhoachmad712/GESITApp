import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

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
                // Biru navy (#1E3A8A) + turunannya — warna dasar aplikasi
                unm: {
                    50: '#EEF2FA',
                    100: '#D7E0F2',
                    200: '#AFC2E5',
                    300: '#7E9BD3',
                    400: '#4D70BD',
                    500: '#2B4DA3',
                    600: '#1E3A8A',
                    700: '#182E6E',
                    800: '#122353',
                    900: '#0C173A',
                },
            },
        },
    },

    plugins: [forms, typography],
};
