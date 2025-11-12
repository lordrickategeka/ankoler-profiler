import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography, require('daisyui')],

    daisyui: {
        themes: [
            {
                profiler: {
                    "primary": "#982B55",      // Brand primary color
                    "secondary": "#982B55",    // Brand secondary color
                    "accent": "#f97316",       // Main accent color
                    "neutral": "#374151",
                    "base-100": "#ffffff",
                    "base-200": "#f3f4f6",     // Background color
                    "info": "#3abff8",         // Button color
                    "success": "#36d399",
                    "warning": "#fbbd23",
                    "error": "#f87272",
                },
            },
            "light",
        ],
        base: true,
        styled: true,
        utils: true,
    },
};
