import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/nav.css',
                'resources/css/custom.css', // ✅ Add this
                'resources/js/app.js',
                'resources/js/nav.js',
                'resources/js/custom.js',
                'resources/js/guest.js',
                'resources/css/guest.css',
                'resources/js/welcome.js',
                'resources/css/welcome.css',  // ✅ And this
            ],
            refresh: true,
        }),
    ],
});
