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
                'primary': '#005693',
                'molveno-blue-100': '#66b6dc',
                'molveno-blue-300': '#309bcf',
                'molveno-blue-500': '#0084c4',
                'molveno-blue-700': '#006ead',

            }
        },
    },

    plugins: [forms],
};
