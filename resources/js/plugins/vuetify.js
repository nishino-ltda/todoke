// Vuetify
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { aliases, mdi } from 'vuetify/iconsets/mdi';

export default createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: {
            mdi,
        },
    },
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary: '#FF3F33',
                    'on-primary': '#FFFFFF',
                    secondary: '#075B5E',
                    surface: '#FFFFFF',
                    background: '#FFE6E1',
                    success: '#9FC87E',
                },
            },
            dark: {
                colors: {
                    primary: '#FF3F33',
                    'on-primary': '#FFFFFF',
                    secondary: '#075B5E',
                },
            },
        },
    },
});
