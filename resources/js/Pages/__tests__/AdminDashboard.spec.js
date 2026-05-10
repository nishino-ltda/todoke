import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import Dashboard from '../Admin/Dashboard.vue'
import adminService from '../../services/admin'

// Mock vue-chartjs to avoid Chart.js DOM errors in happy-dom test environment
vi.mock('vue-chartjs', () => ({
  Line: { template: '<canvas data-cy="activity-chart"></canvas>', props: ['data', 'options'] },
  Bar: { template: '<canvas data-cy="registrations-chart"></canvas>', props: ['data', 'options'] }
}))

// Mock chart.js registration
vi.mock('chart.js', () => ({
  Chart: { register: vi.fn() },
  CategoryScale: {},
  LinearScale: {},
  PointElement: {},
  LineElement: {},
  BarElement: {},
  Title: {},
  Tooltip: {},
  Legend: {},
  Filler: {}
}))

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
        quickActions: 'Ações Rápidas',
        chart: {
          title: 'Atividade do Sistema',
          registrations: 'Novos Registros',
          deliveries: 'Entregas'
        },
        filters: {
          today: 'Hoje',
          '7days': '7 dias',
          '30days': '30 dias',
          all: 'Tudo'
        },
        metrics: {
          totalUsers: 'Total de Usuários',
          activeDeliveries: 'Entregas Ativas',
          systemNodes: 'Nós do Sistema',
          issuesReported: 'Problemas Relatados'
        },
        reviewNewUsers: 'Revisar Novos Usuários',
        approvePendingNodes: 'Aprovar Nós Pendentes',
        systemHealth: 'Saúde do Sistema'
      },
      regions: {
        title: 'Gerenciar Regiões'
      }
    }
  },
  'en': {
    admin: {
      dashboard: {
        title: 'Admin Dashboard',
        quickActions: 'Quick Actions',
        chart: {
          title: 'System Activity',
          registrations: 'New Registrations',
          deliveries: 'Deliveries'
        },
        filters: {
          today: 'Today',
          '7days': '7 Days',
          '30days': '30 Days',
          all: 'All'
        },
        metrics: {
          totalUsers: 'Total Users',
          activeDeliveries: 'Active Deliveries',
          systemNodes: 'System Nodes',
          issuesReported: 'Issues Reported'
        },
        reviewNewUsers: 'Review New Users',
        approvePendingNodes: 'Approve Pending Nodes',
        systemHealth: 'System Health'
      },
      regions: {
        title: 'Manage Regions'
      }
    }
  }
}

const stubs = {
  AdminLayout: { template: '<div class="admin-layout"><slot /></div>' },
  MetricsWidget: { template: '<div class="metric" :data-cy="$attrs[\'data-cy\']">{{ title }}: {{ value }}</div>', props: ['title', 'value', 'icon', 'color'] },
  VRow: { template: '<div><slot /></div>' },
  VCol: { template: '<div><slot /></div>' },
  VCard: { template: '<div class="v-card"><slot /></div>' },
  VCardTitle: { template: '<div class="v-card-title"><slot /></div>' },
  VCardText: { template: '<div><slot /></div>' },
  VIcon: { template: '<i></i>' },
  VList: { template: '<div><slot /></div>' },
  VListItem: { template: '<div class="v-list-item" @click="$emit(\'click\')">{{ title }}</div>', props: ['title'] },
  VBtnToggle: { template: '<div data-cy="chart-period-filter"><slot /></div>', props: ['modelValue'], emits: ['update:modelValue'] },
  VBtn: { template: '<button :data-cy="$attrs[\'data-cy\']" @click="$emit(\'click\')"><slot /></button>' },
  VProgressCircular: { template: '<div></div>' },
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
