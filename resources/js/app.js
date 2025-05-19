import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { useLogStore } from './stores/log';
import vuetify from './plugins/vuetify';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

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
