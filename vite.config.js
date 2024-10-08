import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/admin/images-preview.js',
                'resources/js/admin/images-actions.js',
                'resources/js/cart.js',
                'resources/js/payment/paypal.js'
            ],
            refresh: true,
        }),
    ],
});
