import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import Dashboard from '../Admin/Dashboard.vue'
import adminService from '../../services/admin'

// Mock admin service
vi.mock('../../services/admin', () => ({
  default: {
    getSystemStats: vi.fn(),
  }
}))

// Mock Inertia
vi.mock('@inertiajs/vue3', () => ({
  router: { visit: vi.fn() }
}))

const messages = {
  'pt-BR': {
    admin: {
      dashboard: {
        title: 'Painel Administrativo',
        systemActivity: 'Atividade do Sistema',
        quickActions: 'Ações Rápidas',
        metrics: {
          totalUsers: 'Total de Usuários',
          activeDeliveries: 'Entregas Ativas'
        }
      }
    }
  },
  'en': {
    admin: {
      dashboard: {
        title: 'Admin Dashboard',
        systemActivity: 'System Activity',
        quickActions: 'Quick Actions',
        metrics: {
          totalUsers: 'Total Users',
          activeDeliveries: 'Active Deliveries'
        }
      }
    }
  }
}

const stubs = {
  AdminLayout: { template: '<div class="admin-layout"><slot /></div>' },
  MetricsWidget: { template: '<div class="metric">{{ title }}: {{ value }}</div>', props: ['title', 'value', 'icon', 'color'] },
  VRow: { template: '<div><slot /></div>' },
  VCol: { template: '<div><slot /></div>' },
  VCard: { template: '<div class="v-card"><slot /></div>' },
  VCardTitle: { template: '<div class="v-card-title"><slot /></div>' },
  VCardText: { template: '<div><slot /></div>' },
  VIcon: { template: '<i></i>' },
  VList: { template: '<div><slot /></div>' },
  VListItem: { template: '<div class="v-list-item" @click="$emit(\'click\')">{{ title }}</div>', props: ['title'] }
}

function createWrapper(locale = 'pt-BR') {
  const i18n = createI18n({
    legacy: false,
    locale,
    messages
  })
  return mount(Dashboard, {
    global: {
      plugins: [i18n],
      stubs
    }
  })
}

describe('Admin Dashboard', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    
    adminService.getSystemStats.mockResolvedValue({
      data: {
        total_users: 150,
        active_deliveries: 25,
        total_nodes: 10,
        reported_issues: 2
      }
    })
  })

  it('renders in pt-BR by default', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.text()).toContain('Atividade do Sistema')
    expect(wrapper.text()).toContain('Ações Rápidas')
    expect(wrapper.text()).toContain('Total de Usuários: 150')
  })

  it('renders in en when locale is switched', async () => {
    const wrapper = createWrapper('en')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.text()).toContain('System Activity')
    expect(wrapper.text()).toContain('Quick Actions')
    expect(wrapper.text()).toContain('Total Users: 150')
  })
})
