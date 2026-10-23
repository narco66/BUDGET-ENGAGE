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
                    50:  '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#2563EB',
                    700: '#1D4ED8',
                    800: '#1E40AF',
                    900: '#1E3A8A',
                    950: '#172554',
                    primary:   '#1E3A8A',
                    deep:      '#172554',
                    bright:    '#2563EB',
                    sky:       '#60A5FA',
                    light:     '#DBEAFE',
                    muted:     '#F1F5F9',
                    ink:       '#1E293B',
                    slate:     '#64748B',
                    success:   '#10B981',
                    successBg: '#ECFDF5',
                    warning:   '#F59E0B',
                    warningBg: '#FFFBEB',
                    danger:    '#EF4444',
                    dangerBg:  '#FEF2F2',
                    info:      '#0EA5E9',
                    infoBg:    '#F0F9FF',
                    accent:    '#7C3AED',
                    gold:      '#D97706',
                },
            },
            zIndex: {
                dropdown: '40',
                sticky: '30',
                sidebar: '50',
                overlay: '40',
                modal: '60',
                tooltip: '70',
                toast: '80',
            },
            borderRadius: {
                institutional: '10px',
                xl2: '1rem',
                '2xl': '1rem',
                '3xl': '1.5rem',
            },
            boxShadow: {
                soft:    '0 4px 24px -4px rgba(30,58,138,0.12), 0 8px 16px -8px rgba(0,0,0,0.08)',
                card:    '0 1px 3px rgba(30,58,138,0.08), 0 4px 12px rgba(0,0,0,0.06)',
                hover:   '0 8px 30px -6px rgba(30,58,138,0.22), 0 4px 12px -2px rgba(0,0,0,0.10)',
                sidebar: '4px 0 24px -4px rgba(30,58,138,0.18)',
                kpi:     '0 2px 8px -1px rgba(30,58,138,0.10), 0 8px 24px -6px rgba(30,58,138,0.12)',
                btn:     '0 2px 8px -1px rgba(37,99,235,0.35)',
                float:   '0 20px 60px -12px rgba(30,58,138,0.30)',
                glow:    '0 0 0 3px rgba(37,99,235,0.18)',
                inner:   'inset 0 2px 4px 0 rgba(0,0,0,0.06)',
            },
            maxWidth: {
                '8xl': '88rem',
                '9xl': '96rem',
            },
            spacing: {
                sidebar: '16rem',
                'sidebar-sm': '4.5rem',
            },
            transitionDuration: {
                '250': '250ms',
            },
            animation: {
                'fade-in':      'fadeIn 0.5s ease-out',
                'slide-up':     'slideUp 0.4s ease-out',
                'slide-right':  'slideRight 0.3s ease-out',
                'pulse-slow':   'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
                'bounce-slow':  'bounce 2s infinite',
                'spin-slow':    'spin 3s linear infinite',
                'count-up':     'countUp 1s ease-out forwards',
                'shimmer':      'shimmer 2s linear infinite',
            },
            keyframes: {
                fadeIn:    { from: { opacity: '0' },                          to: { opacity: '1' } },
                slideUp:   { from: { opacity: '0', transform: 'translateY(12px)' }, to: { opacity: '1', transform: 'translateY(0)' } },
                slideRight:{ from: { opacity: '0', transform: 'translateX(-12px)' }, to: { opacity: '1', transform: 'translateX(0)' } },
                countUp:   { from: { opacity: '0', transform: 'translateY(8px)' },  to: { opacity: '1', transform: 'translateY(0)' } },
                shimmer: {
                    '0%':   { backgroundPosition: '-200% center' },
                    '100%': { backgroundPosition: '200% center' },
                },
            },
            backgroundImage: {
                'ceeac-gradient':     'linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%)',
                'ceeac-hero':         'linear-gradient(135deg, #172554 0%, #1E3A8A 40%, #2563EB 100%)',
                'sidebar-gradient':   'linear-gradient(180deg, #172554 0%, #1E3A8A 60%, #1d4ed8 100%)',
                'card-shimmer':       'linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.4) 50%, transparent 100%)',
                'success-gradient':   'linear-gradient(135deg, #059669 0%, #10B981 100%)',
                'warning-gradient':   'linear-gradient(135deg, #D97706 0%, #F59E0B 100%)',
                'danger-gradient':    'linear-gradient(135deg, #DC2626 0%, #EF4444 100%)',
                'accent-gradient':    'linear-gradient(135deg, #6D28D9 0%, #7C3AED 100%)',
                'dot-pattern':        'radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0)',
                'grid-pattern':       'linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)',
            },
        },
    },

    plugins: [forms],
};
