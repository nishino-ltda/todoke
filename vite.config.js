import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: [
            {
                find: '@/',
                replacement: path.resolve(__dirname, './resources/js/')
            },
            {
                find: '@',
                replacement: path.resolve(__dirname, './resources/js')
            },
            {
                find: '@components',
                replacement: path.resolve(__dirname, './resources/js/components')
            },
            {
                find: '@stores', 
                replacement: path.resolve(__dirname, './resources/js/stores')
            },
            {
                find: '@services',
                replacement: path.resolve(__dirname, './resources/js/services')
            }
        ]
    }
});
