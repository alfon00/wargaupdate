import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/letter-signature.js',
                'resources/js/letter-compose.js',
                'resources/js/rt-delete-signature.js',
                'resources/js/rt-applications-settings.js',
                'resources/js/rt-household-registration.js',
                'resources/js/pendataan-ulang.js',
                'resources/js/pendataan-warga.js',
                'resources/js/rt-data-warga-table.js',
                'resources/js/rt-household-members-panel.js',
                'resources/js/pendataan-warga-face.js',
                'resources/js/panel-menu.js',
                'resources/js/panel-user-menu.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600, 700, 800],
                }),
                bunny('Plus Jakarta Sans', {
                    weights: [600, 700, 800],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
