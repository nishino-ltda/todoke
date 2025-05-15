import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createPinia } from 'pinia'
import vuetify from './plugins/vuetify'
import 'vuetify/styles'
import { ZiggyVue } from 'ziggy-js'
import { Ziggy } from './ziggy'

createInertiaApp({
  resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(createPinia())
      .use(vuetify)
      .use(ZiggyVue, Ziggy)

    app.mount(el)
  },
})
