import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/tailwind.css',
                'resources/css/modern-design-system.css',
                'resources/css/modern-navigation.css',
                'resources/css/modern-components.css',
                'resources/js/app.js',
                'resources/js/modern-ux-system.js'
            ],
            refresh: true,
            fonts: [
                bunny('Inter', {
                    weights: [300, 400, 500, 600, 700],
                    display: 'swap',
                }),
                bunny('Space Grotesk', {
                    weights: [500, 600, 700],
                    display: 'swap',
                }),
            ],
        }),
    ],
    optimizeDeps: {
        include: [
            'chart.js',
            'alpinejs',
            'axios'
        ],
    },
    build: {
        cssCodeSplit: true,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        hmr: {
            overlay: false
        }
    },
    resolve: {
        alias: {
            '@': '/resources/js',
            '@css': '/resources/css',
            '@components': '/resources/views/components'
        }
    }
});
