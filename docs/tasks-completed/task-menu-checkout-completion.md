# Task: Complete Menu → Cart → Checkout Customer Flow

## Objective

Complete the customer ordering flow (Menu browsing → Cart management → Checkout) with full i18n (pt-BR default), comprehensive unit tests, and API integration following existing codebase patterns.

## Scope

### Files to Modify

**Components** (in `resources/js/Components/`):
- `ProductList.vue` - Add i18n to search/filter UI, empty states, category labels
- `ProductCard.vue` - Add i18n to price labels, "out of stock" text, aria-labels
- `ProductDetailsModal.vue` - Add i18n to addon section titles, quantity labels, "add to cart" button
- `CartIcon.vue` - Add i18n to aria-labels, tooltip text
- `CheckoutForm.vue` - Add i18n to form labels, placeholders, validation messages, submit button, order summary

**Pages** (in `resources/js/Pages/Customer/`):
- `Menu.vue` - Add i18n to page title, breadcrumbs, loading states, error messages
- `Checkout.vue` - Add i18n to step indicators, confirmation messages, error handling

**Stores** (in `resources/js/stores/`):
- `cart.js` - Add i18n to any hardcoded cart messages/notifications

**Tests** (in respective `__tests__/` directories):
- `ProductList.spec.js` - Add i18n-aware tests
- `ProductCard.spec.js` - Add i18n-aware tests
- `ProductDetailsModal.spec.js` - Add i18n-aware tests
- `CartIcon.spec.js` - Add i18n-aware tests
- `CheckoutForm.spec.js` (in `Components/__tests__/`) - Add/enhance i18n tests
- `Checkout.spec.js` (in `Pages/Customer/__tests__/`) - Add i18n functionality tests
- `menu.spec.js` - Add i18n component tests

### Translation Files (in `resources/lang/`):
- Add any missing translation keys for menu/cart/checkout to existing JSON files
- Follow existing key naming convention: `menu.*`, `cart.*`, `checkout.*`

## Context

### Project Architecture
- **Framework**: Laravel + Inertia + Vue 3 + Vuetify 3
- **State**: Pinia stores
- **Testing**: Vitest + Vue Test Utils (unit), PHPUnit (backend)
- **Routing**: Inertia-based, pages in `resources/js/Pages/`
- **I18n**: Vue I18n already integrated, pt-BR is default, English fallback

### Existing Patterns (already established and verified working)

**Components use `$t()` for all UI text:**
```vue
// resources/js/Components/AppHeader.vue - working i18n pattern
<v-app-bar-title>{{ $t('app.name') }}</v-app-bar-title>
<v-btn>{{ $t('nav.login') }}</v-btn>
```

**Translation files use JSON format:**
```json
// resources/lang/pt-BR.json
{
  "app.name": "TODOKE",
  "nav.login": "Entrar",
  "nav.register": "Cadastrar"
}
```

**Component test pattern with i18n:**
```js
// resources/js/Components/__tests__/AppHeader.spec.js - working pattern
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  locale: 'pt-BR',
  messages: {
    'pt-BR': { /* test messages */ },
    en: { /* fallback messages */ }
  }
})

const wrapper = mount(Component, {
  global: { plugins: [i18n, /* other plugins */] }
})
```

### Key Architecture Details
- All components use `<script setup>` with Composition API
- Components receive data via props (Inertia page props or passed from parent)
- Services (in `resources/js/services/`) handle API calls
- Stores (in `resources/js/stores/`) manage frontend state
- Tests use `vi.mock()` for service mocking (see existing tests for patterns)
- Vuetify components are used for UI elements (`v-btn`, `v-card`, `v-text-field`, etc.)

### Test Requirements
Each test file must:
1. Import and configure `createI18n` with test messages for both pt-BR and en
2. Mount components with the i18n plugin
3. Test rendering with pt-BR locale (verify Portuguese text appears)
4. Test rendering with en locale (verify English text appears)
5. Test fallback behavior when translation key is missing
6. Follow existing test patterns (mocking services, providing stubs for Vuetify components)

### Deliverables
1. All menu/cart/checkout components using `$t()` for every UI string
2. Translation keys added to language files
3. Comprehensive test files for each component covering:
   - Rendering with default locale (pt-BR)
   - Language switching (pt-BR ↔ en)
   - Fallback behavior
   - Interactive functionality (add to cart, form validation, etc.)
4. All tests passing: `npx vitest run`

## Do NOT
- Change component logic or behavior (only add i18n)
- Change API service/store behavior
- Refactor existing code structure
- Create new files unless absolutely necessary
- Modify backend PHP files
- Modify router configuration
