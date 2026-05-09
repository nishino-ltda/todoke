# Task: Courier Dashboard i18n + Testing Completion

## Objective

Integrate i18n into the Courier Dashboard page and enhance existing unit tests to verify i18n behavior, following the same pattern established in Menu/Checkout and Partner Dashboard tasks.

## Background

The Courier Dashboard (`Dashboard.vue`, 234 lines) is fully built with all features (availability toggle, active delivery display, available deliveries list, status updates, delivery stages) but **all UI text is hardcoded in English**. An existing test file (`CourierDashboard.spec.js`, 4 tests) needs i18n-aware enhancements. Several sub-pages are stubs or use hardcoded text.

## Scope

### Files to Modify

**Pages:**
1. `resources/js/Pages/Courier/Dashboard.vue` — Main courier dashboard:
   - "CURRENT STATUS" / "Online" / "Offline" labels
   - "ACTIVE DELIVERY" section header
   - Status chip text (status values need translation)
   - "PICKUP" / "DROPOFF" location labels
   - Delivery ID label ("#ID")
   - "AVAILABLE DELIVERIES" section header
   - Delivery info labels (distance, estimated time, payout)
   - "ACCEPT" / "REJECT" button text
   - "DELIVERY STAGES" section header
   - Stage labels (picked_up, in_transit, delivered)
   - "UPDATE STATUS" button text
   - "No active delivery" empty state
   - "No available deliveries" empty state
   - Loading text, error messages
   - All aria-labels for accessibility
   - Price/distance/time formatting (use locale-aware)

2. `resources/js/Pages/Courier/Courier.vue` (stub) — Add i18n
3. `resources/js/Pages/Courier/Dashboard/Index.vue` (stub) — Add i18n, "Courier Dashboard" title
4. `resources/js/Pages/Courier/Deliveries/Index.vue` (stub) — Add i18n, "Courier Deliveries" title
5. `resources/js/Pages/Courier/Deliveries/Show.vue` (stub) — Add i18n, "Courier Delivery Details" title
6. `resources/js/Pages/Courier/HybridDeliveries/Index.vue` (stub) — Add i18n
7. `resources/js/Pages/Courier/HybridDeliveries/Show.vue` (stub) — Add i18n

**Tests:**
8. `resources/js/Pages/__tests__/CourierDashboard.spec.js` — Add i18n assertions:
   - Verify pt-BR text renders (Portuguese status labels, section headers, buttons)
   - Verify language switching renders English equivalents
   - Test locale-aware formatting (currency, distance, time)

**Translation Files:**
9. `resources/lang/pt-BR.json` — Add `courier.*` translation keys
10. `resources/lang/en.json` — Add `courier.*` translation keys

### Translation Key Structure

```json
{
  "courier": {
    "status": {
      "title": "STATUS ATUAL",
      "online": "Online",
      "offline": "Offline"
    },
    "activeDelivery": {
      "title": "ENTREGA ATIVA",
      "id": "ID",
      "pickup": "RETIRADA",
      "dropoff": "ENTREGA",
      "noDelivery": "Nenhuma entrega ativa"
    },
    "availableDeliveries": {
      "title": "ENTREGAS DISPONÍVEIS",
      "empty": "Nenhuma entrega disponível",
      "distance": "Distância",
      "estimatedTime": "Tempo Estimado",
      "payout": "Valor",
      "accept": "Aceitar",
      "reject": "Recusar"
    },
    "stages": {
      "title": "ETAPAS DA ENTREGA",
      "picked_up": "Coletado",
      "in_transit": "Em Trânsito",
      "delivered": "Entregue"
    },
    "actions": {
      "updateStatus": "Atualizar Status"
    }
  }
}
```

## Patterns to Follow

### Component i18n Pattern (Dashboard.vue is `<script setup>`)
```vue
<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>
```

Replace hardcoded strings in templates:
```diff
- <div class="text-subtitle-2 opacity-70">CURRENT STATUS</div>
+ <div class="text-subtitle-2 opacity-70">{{ t('courier.status.title') }}</div>

- <div class="text-h5 font-weight-bold">{{ isOnline ? 'Online' : 'Offline' }}</div>
+ <div class="text-h5 font-weight-bold">{{ isOnline ? t('courier.status.online') : t('courier.status.offline') }}</div>

- <v-icon color="primary" class="mr-2">mdi-navigation-variant</v-icon> ACTIVE DELIVERY
+ <v-icon color="primary" class="mr-2">mdi-navigation-variant</v-icon> {{ t('courier.activeDelivery.title') }}

- <span class="text-caption text-grey">#{{ activeDelivery.id }}</span>
+ <span class="text-caption text-grey">{{ t('courier.activeDelivery.id') }} {{ activeDelivery.id }}</span>

- <div class="text-caption text-grey">PICKUP</div>
+ <div class="text-caption text-grey">{{ t('courier.activeDelivery.pickup') }}</div>
```

### Price/Distance Formatting
```js
const formatCurrency = (value) => {
  return new Intl.NumberFormat(t('locale'), { style: 'currency', currency: 'BRL' }).format(value)
}
```

### Test Pattern (follow existing CourierDashboard.spec.js + i18n pattern)
```js
import { createI18n } from 'vue-i18n'

const ptBrMessages = {
  courier: {
    status: { title: 'STATUS ATUAL', online: 'Online', offline: 'Offline' },
    activeDelivery: { title: 'ENTREGA ATIVA', id: 'ID', pickup: 'RETIRADA', dropoff: 'ENTREGA' },
    // ... all keys used in the component
  }
}

function createWrapper(locale = 'pt-BR', messages = ptBrMessages) {
  const i18n = createI18n({ locale, messages: { [locale]: messages } })
  return mount(Component, {
    global: { plugins: [i18n, /* pinia, stubs, vuetify */] }
  })
}
```

### Stage/Status Translation Mapping
Status values from the API (e.g., `picked_up`, `in_transit`, `delivered`, `pending`, `accepted`) should map through translation keys:
```vue
<v-chip :color="getStatusColor(item.status)" size="small" class="text-uppercase font-weight-bold">
  {{ t(`courier.status.${activeDelivery.status}`) }}
</v-chip>
```

Add corresponding entries in translation files:
```json
"status": {
  "pending": "Pendente",
  "accepted": "Aceita",
  "picked_up": "Coletada",
  "in_transit": "Em Trânsito",
  "delivered": "Entregue",
  "cancelled": "Cancelada"
}
```

## Key Caveats
- **Do NOT** change component behavior, API calls, business logic, or component interfaces
- **Do NOT** refactor existing code structure
- **Do NOT** modify router configuration or backend PHP
- **Do NOT** implement the Map component (it's a separate task)
- Preserve all existing `data-cy` and `data-test` attributes
- Preserve existing event handlers and prop interfaces
- Stub pages (Courier.vue, Dashboard/Index.vue, Deliveries/*, HybridDeliveries/*) should only get i18n — no new functionality

## Verification

```bash
npx vitest run resources/js/Pages/__tests__/CourierDashboard.spec.js
```

Review the Courier Dashboard page to confirm all text renders correctly in pt-BR (default) and after switching to English.
