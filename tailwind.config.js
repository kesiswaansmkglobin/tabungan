import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy: {
                    50: '#f0f3f9',
                    100: '#d9e0f0',
                    200: '#b3c1e1',
                    300: '#8da2d2',
                    400: '#6683c3',
                    500: '#1e3a5f',
                    600: '#1a3354',
                    700: '#162b49',
                    800: '#12233e',
                    900: '#0e1b33',
                    950: '#0a1328',
                },
                gold: {
                    50: '#fdf8ed',
                    100: '#f9edcc',
                    200: '#f3db99',
                    300: '#edc966',
                    400: '#e7b733',
                    500: '#d4a520',
                    600: '#b8911c',
                    700: '#9c7d18',
                    800: '#806914',
                    900: '#645510',
                    950: '#48410c',
                },
            },
        },
    },

    safelist: [
        'bg-purple-100', 'text-purple-700', 'dark:bg-purple-900/30', 'dark:text-purple-300',
        'bg-blue-100', 'text-blue-700', 'dark:bg-blue-900/30', 'dark:text-blue-300',
        'bg-emerald-100', 'text-emerald-700', 'dark:bg-emerald-900/30', 'dark:text-emerald-300',
        'bg-gold-100', 'text-gold-700', 'dark:bg-gold-900/30', 'dark:text-gold-300',
        'bg-navy-100', 'text-navy-700', 'dark:bg-navy-700', 'dark:text-navy-200',
        'bg-red-100', 'text-red-700', 'dark:bg-red-900/30', 'dark:text-red-300',
        'bg-gray-100', 'text-gray-600', 'dark:bg-gray-700', 'dark:text-gray-400',
    ],

    plugins: [forms],
};
