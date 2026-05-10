# Task: Partner Dashboard i18n + Testing Completion

## Objective

Integrate i18n into all Partner Dashboard pages and enhance existing unit tests to verify i18n behavior, following the same pattern established in the Menu/Checkout flow (docs/task-menu-checkout-completion.md).

## Background

All partner CRUD pages are built, the partner service has all API methods, and test files exist for the main pages. However, **zero partner pages use i18n** — all UI text is hardcoded in English. This task adds `$t()` / `useI18n()` to every partner page and enhances tests with i18n verification.

## Scope

### Files to Modify

**Pages** (in `resources/js/Pages/Partner/`):
1. `Dashboard.vue` — Metrics labels, "Recent Orders" title, status chip text, action button labels, empty state, loading text
2. `Orders.vue` — "Orders Management", "Refresh" button, table headers, status options, action labels, empty state
3. `OrderDetails.vue` — Order detail labels, status timeline, action buttons
4. `Products/Index.vue` — Page title, "Add Product" button, table headers, search/filter, empty state
5. `Products/Create.vue` — Form labels, validation messages, submit/cancel buttons
6. `Products/Edit.vue` — Form labels, validation messages, submit/cancel buttons
7. `Addons/Index.vue` — Page title, "Add Addon" button, table headers, empty state
8. `Addons/Create.vue` — Form labels, validation messages, submit/cancel buttons
9. `Addons/Edit.vue` — Form labels, validation messages, submit/cancel buttons
10. `Regions/Index.vue` — Page title, "Add Region" button, table headers, empty state
11. `Regions/Create.vue` — Form labels, validation messages, submit/cancel buttons
12. `Regions/Edit.vue` — Form labels, validation messages, submit/cancel buttons
**Tests** (in `resources/js/Pages/__tests__/`):
16. `PartnerDashboard.spec.js` — Add i18n assertions (verify pt-BR text renders, language switching)
17. `PartnerLogistics.spec.js` — Add i18n assertions for Regions pages
18. `PartnerProducts.spec.js` — Add i18n assertions for Products pages

**Component** (in `resources/js/Components/`):
19. `MetricsWidget.vue` — Add i18n support if not present

**Translation Files** (in `resources/lang/`):
20. `pt-BR.json` — Add `partner.*` translation keys
21. `en.json` — Add `partner.*` translation keys

## Patterns to Follow

### i18n in Script Setup (Vue 3 Composition API)
```vue
<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>
```

### i18n in Templates
- Replace hardcoded strings with `{{ t('partner.dashboard.title') }}`
- For Vuetify component props: `:title="t('partner.orders.title')"`
- For button text: `{{ t('partner.products.add') }}`
- For status chips: use translation keys with dynamic status values
- For empty states: `{{ t('partner.products.empty') }}`
- For aria-labels: `:aria-label="t('partner.actions.view')"`

### Price Formatting
- Replace `'$'` with `t('common.currency')` or use locale-aware formatting
- Use `new Intl.NumberFormat()` or a shared currency utility

### Test Pattern (follow CartIcon.spec.js / ProductCard.spec.js style)
```js
import { createI18n } from 'vue-i18n'

const ptBrMessages = { partner: { dashboard: { title: 'Painel do Parceiro' } } }
const enMessages = { partner: { dashboard: { title: 'Partner Dashboard' } } }

function createWrapper(locale = 'pt-BR', messages = ptBrMessages) {
  const i18n = createI18n({ locale, messages: { [locale]: messages } })
  return mount(Component, {
    global: { plugins: [i18n, /* pinia, stubs */] }
  })
}
```

## Translation Key Naming Convention
```
partner.{page}.{element} — e.g.:
  partner.dashboard.title
  partner.dashboard.recentOrders
  partner.orders.title
  partner.products.title
  partner.products.add
  partner.products.form.name
  partner.products.form.price
  partner.products.empty
  partner.addons.title
  partner.regions.title
  partner.actions.edit
  partner.actions.delete
  partner.actions.save
  partner.actions.cancel
  partner.actions.view
  partner.status.pending
  partner.status.preparing
  partner.status.ready
  partner.status.delivered
  partner.status.cancelled
```

## Key Caveats
- **Do NOT** change component behavior, API calls, or business logic
- **Do NOT** refactor existing code structure
- **Do NOT** modify router configuration or backend PHP
- **Do NOT** change test stubs/mocks that already work (only add i18n assertions)
- Preserve all existing `data-cy` and `data-test` attributes
- Preserve existing event handlers and prop interfaces

## Verification

All tests pass:
```bash
npx vitest run resources/js/Pages/__tests__/PartnerDashboard.spec.js resources/js/Pages/__tests__/PartnerLogistics.spec.js resources/js/Pages/__tests__/PartnerProducts.spec.js
```

Review each partner page in a browser/dev environment to confirm translations render correctly for pt-BR (default) and English (after switching).
