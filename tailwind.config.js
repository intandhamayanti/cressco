import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './resources/views/**/*.blade.php',
        './vendor/livewire/flux-pro/stubs/**/*.blade.php',
        './vendor/livewire/flux/stubs/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"DM Sans"', ...defaultTheme.fontFamily.sans],
                'dm-sans': ['"DM Sans"', ...defaultTheme.fontFamily.sans],
            },
            spacing: {
                '4.5': '1.125rem',
                '13': '3.25rem',
            },
            boxShadow: {
                '2xs': '0 1px 2px 0 rgba(16, 24, 40, 0.05)',
                'xs': '0 1px 2px 0 rgba(16, 24, 40, 0.05)',
            },
            fontSize: {
                // Display Scales (Figma Specification)
                'display-2xl': ['72px', { lineHeight: '90px' }],
                'display-xl': ['60px', { lineHeight: '72px' }],
                'display-lg': ['48px', { lineHeight: '60px' }],
                'display-md': ['36px', { lineHeight: '44px' }],
                'display-sm': ['30px', { lineHeight: '38px' }],
                'display-xs': ['24px', { lineHeight: '32px' }],
                // Text Scales (Figma Specification)
                'text-xl': ['20px', { lineHeight: '30px' }],
                'text-lg': ['18px', { lineHeight: '28px' }],
                'text-md': ['16px', { lineHeight: '24px' }],
                'text-sm': ['14px', { lineHeight: '20px' }],
                'text-xs': ['12px', { lineHeight: '18px' }],
            },
            colors: {
                // Terracotta (Brand / Primary)
                terracotta: {
                    50: '#FCF5F3',
                    100: '#FAE0DA',
                    200: '#F3B9AA',
                    300: '#E88C74',
                    400: '#DE6544',
                    500: '#CC4420',
                    600: '#B5381B',
                    700: '#852918',
                    800: '#611C10',
                    900: '#3B110B',
                },
                // Semantic Primary
                primary: {
                    50: '#FCF5F3',
                    100: '#FAE0DA',
                    200: '#F3B9AA',
                    300: '#E88C74',
                    400: '#DE6544',
                    500: '#CC4420',
                    600: '#B5381B',
                    700: '#852918',
                    800: '#611C10',
                    900: '#3B110B',
                },
                // Semantic Info (Blue)
                info: {
                    50: '#F0F5FF',
                    100: '#DBE8FE',
                    200: '#BFD7FD',
                    300: '#93BBFA',
                    400: '#669EF8',
                    500: '#367EE9',
                    600: '#2B66C4',
                    700: '#234F94',
                    800: '#1B365D',
                    900: '#0F1E36',
                },
                // Semantic Success (Green)
                success: {
                    50: '#F2FAF5',
                    100: '#DCF5E5',
                    300: '#A5E4BA',
                    400: '#6ED68F',
                    500: '#32C75E',
                    600: '#1EAA4A',
                    700: '#168B3C',
                },
                // Semantic Warning (Amber/Yellow)
                warning: {
                    50: '#FEF9EC',
                    100: '#FDF2D5',
                    300: '#F9D788',
                    400: '#F6C153',
                    500: '#F3AA1C',
                    600: '#D99411',
                    700: '#B87A0B',
                },
                // Semantic Error (Red)
                error: {
                    50: '#FDF4F4',
                    100: '#F9DCDC',
                    300: '#EAA7A7',
                    400: '#E27C7C',
                    500: '#D9534F',
                    600: '#C53B3B',
                    700: '#912018',
                },
                // Grayscale / Neutrals
                gray: {
                    0: '#FFFFFF',
                    25: '#FCFCFD',
                    50: '#F8F9FA',
                    100: '#F2F4F7',
                    200: '#EAECF0',
                    300: '#D0D5DD',
                    400: '#98A2B3',
                    500: '#667085',
                    600: '#475467',
                    700: '#344054',
                    800: '#1D2939',
                    900: '#101828',
                },
                // Background Tokens
                bg: {
                    primary: '#FFFFFF',
                    secondary: '#F8F9FA',
                },
                // Surface Tokens
                surface: {
                    default: '#FFFFFF',
                    hover: '#F4F5F6',
                    active: '#EEF0F2',
                },
            },
        },
    },

    plugins: [forms],
};
