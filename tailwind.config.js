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
                sans: ['Inter', 'Roboto', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ceeac: {
                    primary: '#1E3A8A',
                    bright: '#2563EB',
                    sky: '#60A5FA',
                    muted: '#F3F4F6',
                    ink: '#374151',
                    success: '#10B981',
                    warning: '#F59E0B',
                    danger: '#EF4444',
                },
            },
            borderRadius: {
                institutional: '10px',
            },
            boxShadow: {
                soft: '0 4px 24px -4px rgba(30, 58, 138, 0.12), 0 8px 16px -8px rgba(0, 0, 0, 0.08)',
                card: '0 1px 3px rgba(30, 58, 138, 0.08), 0 4px 12px rgba(0, 0, 0, 0.06)',
            },
            maxWidth: {
                '8xl': '88rem',
            },
        },
    },

    plugins: [forms],
};
