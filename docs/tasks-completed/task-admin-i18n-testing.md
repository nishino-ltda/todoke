# Task: Admin Panel i18n + Testing

## Objective

Integrate i18n into the 3 built Admin Panel pages (Dashboard, Users, Nodes) and create comprehensive unit tests with i18n assertions. No test files exist for the admin area — they must be created from scratch following patterns from PartnerDashboard.spec.js and CourierDashboard.spec.js.

## Background

The Admin Panel has 3 fully built pages and 3 stub pages (Regions, Deliveries, Settings — excluded from this task). **Zero pages use i18n** and **zero test files exist**. The admin service (`admin.js`) has all required API methods.

## Scope

### Files to Modify

**Pages:**
1. `resources/js/Pages/Admin/Dashboard.vue` (98 lines) — Metrics labels, "System Activity" title, "Quick Actions" title, action item text ("Review New Users", "Approve Pending Nodes", etc.), empty state text, chart placeholder text, price formatting, all aria-labels

2. `resources/js/Pages/Admin/Users.vue` (134 lines) — "User Management" title, "Add User" button, table headers, search placeholder, role/filter labels, status chip text, activate/deactivate button text, confirmation modal text, user detail labels, empty state, loading text, error messages

3. `resources/js/Pages/Admin/Nodes.vue` (131 lines) — "Node Approval & Management" title, table headers, status chip text, approve/reject button text, search/filter labels, node detail labels, confirmation modal text, empty state, loading text, error messages

**Tests (create new files):**
4. `resources/js/Pages/__tests__/AdminDashboard.spec.js` — Test Dashboard.vue:
   - Renders with pt-BR locale (verify Portuguese metric labels, section titles, action items)
   - Renders with en locale (verify English equivalents)
   - Fallback behavior when translation key missing

5. `resources/js/Pages/__tests__/AdminUsers.spec.js` — Test Users.vue:
   - Renders with pt-BR locale (verify "Gerenciamento de Usuários", status labels, button text)
   - Language switching verification
   - User list rendering with translated status chips

6. `resources/js/Pages/__tests__/AdminNodes.spec.js` — Test Nodes.vue:
   - Renders with pt-BR locale (verify "Aprovação e Gerenciamento de Nós", status labels)
   - Language switching verification
   - Approve/reject button text in both locales

**Translation Files:**
7. `resources/lang/pt-BR.json` — Add `admin.*` translation keys
8. `resources/lang/en.json` — Add `admin.*` translation keys

### Out of Scope (NOT to be modified)
- `Admin/Regions.vue`, `Admin/Deliveries.vue`, `Admin/Settings.vue` (stub pages)
- `Admin/Regions/`, `Admin/Deliveries/`, `Admin/Settings/`, `Admin/Nodes/`, `Admin/Users/` subdirectory stubs
- Router configuration
- Backend PHP files
- Admin service (already has API methods)

## Translation Key Structure
```json
{
  "admin": {
    "dashboard": {
      "title": "Painel Administrativo",
      "systemActivity": "Atividade do Sistema",
      "quickActions": "Ações Rápidas",
      "reviewNewUsers": "Revisar Novos Usuários",
      "approvePendingNodes": "Aprovar Nós Pendentes",
      "monitorDeliveries": "Monitorar Entregas",
      "systemSettings": "Configurações do Sistema",
      "chartPlaceholder": "Gráficos de atividade em tempo real serão integrados aqui."
    },
    "users": {
      "title": "Gerenciamento de Usuários",
      "addUser": "Adicionar Usuário",
      "search": "Buscar usuários...",
      "table": {
        "name": "Nome",
        "email": "E-mail",
        "role": "Função",
        "status": "Status",
        "created": "Criado em",
        "actions": "Ações"
      },
      "actions": {
        "activate": "Ativar",
        "deactivate": "Desativar",
        "view": "Visualizar",
        "confirmActivate": "Tem certeza que deseja ativar este usuário?",
        "confirmDeactivate": "Tem certeza que deseja desativar este usuário?"
      },
      "empty": "Nenhum usuário encontrado"
    },
    "nodes": {
      "title": "Aprovação e Gerenciamento de Nós",
      "search": "Buscar nós...",
      "table": {
        "name": "Nome",
        "type": "Tipo",
        "status": "Status",
        "owner": "Proprietário",
        "region": "Região",
        "actions": "Ações"
      },
      "actions": {
        "approve": "Aprovar",
        "reject": "Rejeitar",
        "view": "Visualizar",
        "confirmApprove": "Tem certeza que deseja aprovar este nó?",
        "confirmReject": "Tem certeza que deseja rejeitar este nó?"
      },
      "empty": "Nenhum nó encontrado"
    }
  }
}
```

## Patterns to Follow

### Page i18n Pattern
```vue
<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>
```

Replace hardcoded strings in templates:
```diff
- <h1 class="text-h4 font-weight-bold">User Management</h1>
+ <h1 class="text-h4 font-weight-bold">{{ t('admin.users.title') }}</h1>

- <v-btn color="primary">Add User</v-btn>
+ <v-btn color="primary">{{ t('admin.users.addUser') }}</v-btn>
```

For status chips with dynamic values:
```diff
- <v-chip :color="getStatusColor(item.role)">{{ item.role }}</v-chip>
+ <v-chip :color="getStatusColor(item.role)">{{ t(`admin.users.roles.${item.role}`) }}</v-chip>
```

### Test Pattern (CREATE from scratch — no existing test files)
Follow the established pattern from `CourierDashboard.spec.js` or `PartnerDashboard.spec.js`:

```js
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'

// Mock admin service
vi.mock('../../services/admin', () => ({
  default: {
    getSystemStats: vi.fn(),
    getUsers: vi.fn(),
    manageUser: vi.fn()
  }
}))

// Mock Inertia
vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({ url: '/admin' }),
  router: { visit: vi.fn() }
}))

const ptBrMessages = {
  admin: {
    dashboard: { title: 'Painel Administrativo' },
    users: { title: 'Gerenciamento de Usuários' },
    // ... all keys used in the component
  }
}

const enMessages = {
  admin: {
    dashboard: { title: 'Admin Dashboard' },
    users: { title: 'User Management' },
    // ... all keys
  }
}

function createWrapper(locale = 'pt-BR') {
  const messages = locale === 'pt-BR' ? ptBrMessages : enMessages
  const i18n = createI18n({ locale, messages: { [locale]: messages } })
  const pinia = createPinia()
  
  // Add stub for Vuetify components and layout
  const stubs = {
    AdminLayout: { template: '<div><slot /></div>' },
    MetricsWidget: { template: '<div class="metric"><slot /></div>' },
    DataTable: { template: '<div class="data-table"><slot /></div>' },
    VCard: { template: '<div><slot /></div>' },
    VCardTitle: { template: '<div><slot /></div>' },
    VCardText: { template: '<div><slot /></div>' },
    VRow: { template: '<div><slot /></div>' },
    VCol: { template: '<div><slot /></div>' },
    VBtn: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
    VChip: { template: '<span><slot /></span>' },
    VIcon: { template: '<i></i>' },
    VList: { template: '<div><slot /></div>' },
    VListItem: { template: '<div @click="$el.click"><slot /></div>' },
    VTextField: { template: '<input />' },
    VDialog: { template: '<div v-if="modelValue"><slot /></div>', props: ['modelValue'] }
  }

  return mount(Component, {
    global: { plugins: [i18n, pinia], stubs }
  })
}
```

## Verification
```bash
npx vitest run resources/js/Pages/__tests__/AdminDashboard.spec.js resources/js/Pages/__tests__/AdminUsers.spec.js resources/js/Pages/__tests__/AdminNodes.spec.js
```

Review each admin page to confirm all text renders correctly in pt-BR (default) and after switching to English.
