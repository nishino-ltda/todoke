import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { createI18n } from 'vue-i18n';
import { useLogStore } from './stores/log';
import vuetify from './plugins/vuetify';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import en from '../lang/en.json';
import ptBR from '../lang/pt-BR.json';

// Detect browser language
const browserLanguage = navigator.language || navigator.userLanguage;
const supportedLocales = ['en', 'pt-BR'];
const detectedLocale = supportedLocales.includes(browserLanguage) 
    ? browserLanguage 
    : browserLanguage.startsWith('pt') 
        ? 'pt-BR' 
        : 'en';

const i18n = createI18n({
    legacy: false,
    locale: detectedLocale,
    fallbackLocale: 'en',
    messages: {
        en,
        'pt-BR': ptBR
    }
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();

        const mountedApp = app
            .use(plugin)
            .use(pinia)
            .use(vuetify)
            .use(ZiggyVue)
            .use(i18n)
            .mount(el);

        // Expose stores to Cypress after initialization
        if (window.Cypress) {
            const logStore = useLogStore();
            window.__appStores = {
                logStore
            };
        }

        return mountedApp;
    },
    progress: {
        color: '#4B5563',
    },
});
