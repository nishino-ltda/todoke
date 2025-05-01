import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig(({ command, mode }) => {
    const isTest = mode === 'test'
    
    return {
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
        test: {
            css: {
                modules: {
                    classNameStrategy: 'non-scoped'
                }
            },
            environment: 'happy-dom',
            setupFiles: isTest ? ['vitest.setup.js'] : [],
            deps: {
                inline: ['vuetify']
            },
            server: {
                deps: {
                    inline: ['vuetify']
                }
            },
            exclude: [
                '**/node_modules/vuetify/**/*.css',
                '**/node_modules/vuetify/lib/**/*.css',
                '**/node_modules/vuetify/styles/**/*.css'
            ],
            css: {
                modules: false
            }
        },
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
            },
            {
                find: '@pages',
                replacement: path.resolve(__dirname, './resources/js/pages')
            }
            ]
        }
    }
})
