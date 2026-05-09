# Task: Home Page Completion (CTA + Features Integration + i18n + Testing)

## Objective

Complete the Home page by: (1) integrating the existing HomeFeatures component into the page, (2) creating the missing HomeCTA component with i18n, (3) adding i18n to Home.vue itself, and (4) writing tests for the new work.

## Background

The Home page (`Home.vue`) currently only renders `HomeHero`. `HomeFeatures` is fully built with i18n but **never imported or rendered**. `HomeCTA` doesn't exist. The page itself has no i18n.

## Scope

### Files to Modify

1. **`resources/js/Pages/Home.vue`** (16 lines):
   - Import and render `<HomeFeatures />` after `<HomeHero />`
   - Import and render `<HomeCTA />` after `<HomeFeatures />`
   - Import `useI18n` and use `$t()` for any page-level text (if needed)
   - Add responsive container classes if missing

2. **Create `resources/js/Components/HomeCTA.vue`**:
   - A Call-to-Action section with:
     - Title and description (i18n)
     - 3 user-type specific CTA cards (Customer, Partner, Courier):
       - Each card: icon, title, description, link button (register/login)
       - Links to respective registration flows
     - Responsive grid layout (1 col mobile, 3 col desktop)
     - All text via `$t()` using `home.cta.*` keys
   - Pattern to follow: look at `HomeFeatures.vue` for style/patterns

3. **Create `resources/js/Components/__tests__/HomeCTA.spec.js`**:
   - Test renders with pt-BR locale (verify Portuguese text)
   - Test renders with en locale (verify English equivalents)
   - Test all 3 user-type cards render with correct icons and links
   - Test fallback behavior
   - Follow test patterns from `HomeHero.spec.js` / `HomeFeatures.spec.js`

4. **`resources/lang/pt-BR.json`** — Add `home.cta.*` keys
5. **`resources/lang/en.json`** — Add `home.cta.*` keys

## Translation Key Structure
```json
{
  "home": {
    "cta": {
      "title": "Pronto para Começar?",
      "description": "Junte-se à plataforma que está revolucionando as entregas. Escolha seu perfil e comece agora.",
      "customer": {
        "title": "Cliente",
        "description": "Peça de seus restaurantes favoritos com entrega rápida e preço justo.",
        "button": "Quero Pedir"
      },
      "partner": {
        "title": "Parceiro",
        "description": "Divulgue seu cardápio e alcance mais clientes na sua região.",
        "button": "Quero Vender"
      },
      "courier": {
        "title": "Entregador",
        "description": "Trabalhe no seu horário e ganhe dinheiro fazendo entregas.",
        "button": "Quero Entregar"
      }
    }
  }
}
```

## Patterns to Follow

### HomeCTA.vue Pattern
```vue
<template>
  <section class="home-cta py-12" data-test="home-cta">
    <v-container>
      <v-row>
        <v-col cols="12">
          <h2 class="text-h4 text-center mb-4" data-test="cta-title">
            {{ t('home.cta.title') }}
          </h2>
          <p class="text-body-1 text-center mb-8" data-test="cta-description">
            {{ t('home.cta.description') }}
          </p>
        </v-col>
      </v-row>
      <v-row>
        <v-col v-for="cta in ctaItems" :key="cta.key" cols="12" md="4" :data-test="`cta-${cta.key}`">
          <v-card height="100%" class="text-center pa-4">
            <v-icon size="x-large" color="primary" class="mb-4">{{ cta.icon }}</v-icon>
            <v-card-title>{{ t(`home.cta.${cta.key}.title`) }}</v-card-title>
            <v-card-text>{{ t(`home.cta.${cta.key}.description`) }}</v-card-text>
            <v-card-actions class="justify-center pb-4">
              <v-btn color="primary" variant="elevated" :to="cta.route" :data-test="`cta-${cta.key}-btn`">
                {{ t(`home.cta.${cta.key}.button`) }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useLogStore } from '@/stores/log'

const { t } = useI18n()
const logStore = useLogStore()
logStore.log('HomeCTA component initialized')

const ctaItems = [
  { key: 'customer', icon: 'mdi-account', route: route('register') + '?type=customer' },
  { key: 'partner', icon: 'mdi-store', route: route('register') + '?type=partner' },
  { key: 'courier', icon: 'mdi-motorbike', route: route('register') + '?type=courier' }
]
</script>
```

### Test Pattern (follow HomeHero.spec.js / HomeFeatures.spec.js)
```js
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import HomeCTA from '@/Components/HomeCTA.vue'

// Mock Inertia route helper
vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({ url: '/' }),
  router: { visit: vi.fn() }
}))

// Mock the route() global function
global.route = vi.fn(() => '/register')

const ptBrMessages = {
  home: {
    cta: {
      title: 'Pronto para Começar?',
      description: 'Junte-se à plataforma...',
      customer: {
        title: 'Cliente',
        description: 'Peça de seus restaurantes...',
        button: 'Quero Pedir'
      },
      partner: {
        title: 'Parceiro',
        description: 'Divulgue seu cardápio...',
        button: 'Quero Vender'
      },
      courier: {
        title: 'Entregador',
        description: 'Trabalhe no seu horário...',
        button: 'Quero Entregar'
      }
    }
  }
}

const enMessages = {
  home: {
    cta: {
      title: 'Ready to Start?',
      description: 'Join the platform...',
      customer: {
        title: 'Customer',
        description: 'Order from your favorite...',
        button: 'Order Now'
      },
      partner: {
        title: 'Partner',
        description: 'Showcase your menu...',
        button: 'Start Selling'
      },
      courier: {
        title: 'Courier',
        description: 'Work on your schedule...',
        button: 'Start Delivering'
      }
    }
  }
}

function createWrapper(locale = 'pt-BR') {
  const messages = locale === 'pt-BR' ? ptBrMessages : enMessages
  const i18n = createI18n({ locale, messages: { [locale]: messages } })
  const pinia = createPinia()
  setActivePinia(pinia)

  const stubs = {
    VContainer: { template: '<div><slot /></div>' },
    VRow: { template: '<div><slot /></div>' },
    VCol: { template: '<div><slot /></div>' },
    VCard: { template: '<div><slot /></div>' },
    VCardTitle: { template: '<div><slot /></div>' },
    VCardText: { template: '<div><slot /></div>' },
    VCardActions: { template: '<div><slot /></div>' },
    VBtn: { template: '<a :href="to"><slot /></a>', props: ['to'] },
    VIcon: { template: '<i></i>' }
  }

  return mount(HomeCTA, {
    global: { plugins: [i18n, pinia], stubs }
  })
}
```

## Verification
```bash
npx vitest run resources/js/Components/__tests__/HomeCTA.spec.js
```

Review Home page in browser — HomeFeatures and HomeCTA should render below HomeHero, all text in pt-BR by default, switching to English via LanguageSelector.
