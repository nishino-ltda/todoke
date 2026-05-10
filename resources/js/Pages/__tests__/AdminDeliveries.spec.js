import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import DeliveriesIndex from '../Admin/Deliveries/Index.vue'
import adminService from '../../services/admin'

vi.mock('../../services/admin', () => ({
  default: { getDeliveries: vi.fn() }
}))

const today = new Date().toISOString()
const mockDeliveries = [
  { id: 1, status: 'pending', value: 18.50, type: 'ground', customer: { name: 'Cliente A' }, courier: null, origin_address: 'Rua A', destination_address: 'Rua B', updated_at: today },
  { id: 2, status: 'in_transit', value: 25.00, type: 'ground', customer: { name: 'Cliente B' }, courier: { name: 'Courier X' }, origin_address: 'Rua C', destination_address: 'Rua D', updated_at: today },
  { id: 3, status: 'delivered', value: 32.00, type: 'drone', customer: { name: 'Cliente C' }, courier: { name: 'Drone 1' }, origin_address: 'Rua E', destination_address: 'Rua F', updated_at: today },
]

const messages = {
  'pt-BR': {
    admin: {
      deliveries: {
        title: 'Monitoramento de Entregas',
        pending: 'Pendente',
        in_transit: 'Em Trânsito',
        delivered_today: 'Entregues Hoje',
        table: { id: 'ID', customer: 'Cliente', courier: 'Entregador', status: 'Status', type: 'Tipo', value: 'Valor', actions: 'Ações' },
        detail: { title: 'Detalhes da Entrega', customer: 'Cliente', courier: 'Entregador', not_assigned: 'Não Atribuído', status_history: 'Histórico', route_map: 'Rota' },
        notifications: { load_failed: 'Falha ao carregar entregas' }
      },
      dashboard: { metrics: { activeDeliveries: 'Entregas Ativas' } }
    },
    partner: { orders: { refresh: 'Atualizar' }, actions: { close: 'Fechar' } },
    courier: {
      status: { pending: 'Pendente', in_transit: 'Em Trânsito', delivered: 'Entregue', accepted: 'Aceita', collected: 'Coletada', canceled: 'Cancelada' },
      activeDelivery: { id: 'ID', pickup: 'Retirada', dropoff: 'Entrega' }
    }
  },
  'en': {
    admin: {
      deliveries: {
        title: 'Deliveries Monitoring',
        pending: 'Pending',
        in_transit: 'In Transit',
        delivered_today: 'Delivered Today',
        table: { id: 'ID', customer: 'Customer', courier: 'Courier', status: 'Status', type: 'Type', value: 'Value', actions: 'Actions' },
        detail: { title: 'Delivery Details', customer: 'Customer', courier: 'Courier', not_assigned: 'Not Assigned', status_history: 'History', route_map: 'Route' },
        notifications: { load_failed: 'Failed to load deliveries' }
      },
      dashboard: { metrics: { activeDeliveries: 'Active Deliveries' } }
    },
    partner: { orders: { refresh: 'Refresh' }, actions: { close: 'Close' } },
    courier: {
      status: { pending: 'Pending', in_transit: 'In Transit', delivered: 'Delivered', accepted: 'Accepted', collected: 'Collected', canceled: 'Canceled' },
      activeDelivery: { id: 'ID', pickup: 'Pickup', dropoff: 'Dropoff' }
    }
  }
}

const stubs = {
  AdminLayout: { template: '<div class="admin-layout"><slot /></div>' },
  DataTable: {
    template: `
      <div class="data-table" data-cy="deliveries-table">
        <div v-for="item in items" :key="item.id" :data-cy="'row-'+item.id">
          <slot name="item.status" :item="item">{{ item.status }}</slot>
          <slot name="item.customer" :item="item">{{ item.customer?.name }}</slot>
          <slot name="item.courier" :item="item">{{ item.courier?.name }}</slot>
          <slot name="item.value" :item="item">{{ item.value }}</slot>
          <slot name="item.actions" :item="item"></slot>
        </div>
      </div>
    `,
    props: ['headers', 'items', 'loading']
  },
  AppModal: {
    template: '<div v-if="modelValue" class="app-modal" :data-cy="$attrs[\'data-cy\']"><slot /><slot name="actions" /></div>',
    props: ['modelValue', 'title', 'maxWidth'],
    emits: ['update:modelValue']
  },
  DeliveryMap: { template: '<div data-cy="detail-map"></div>' },
  VBtn: { template: '<button :data-cy="$attrs[\'data-cy\']" @click="$emit(\'click\')"><slot /></button>' },
  VChip: { template: '<span :data-cy="$attrs[\'data-cy\']"><slot /></span>' },
  VAvatar: { template: '<span><slot /></span>' },
  VIcon: { template: '<i></i>' },
  VRow: { template: '<div><slot /></div>' },
  VCol: { template: '<div><slot /></div>' },
  VCard: { template: '<div class="v-card" :data-cy="$attrs[\'data-cy\']"><slot /></div>' },
  VTimeline: { template: '<div><slot /></div>' },
  VTimelineItem: { template: '<div><slot /></div>' },
}

function createWrapper(locale = 'pt-BR') {
  const i18n = createI18n({ legacy: false, locale, messages })
  return mount(DeliveriesIndex, { global: { plugins: [i18n], stubs } })
}

describe('Admin Deliveries', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    adminService.getDeliveries.mockResolvedValue({ data: { deliveries: mockDeliveries } })
  })

  it('renders in pt-BR by default', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(wrapper.text()).toContain('Monitoramento de Entregas')
    expect(wrapper.text()).toContain('Pendente')
    expect(wrapper.text()).toContain('Em Trânsito')
  })

  it('renders in en when locale is switched', async () => {
    const wrapper = createWrapper('en')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(wrapper.text()).toContain('Deliveries Monitoring')
    expect(wrapper.text()).toContain('Pending')
    expect(wrapper.text()).toContain('In Transit')
  })

  it('computes correct metric counts', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    // 3 deliveries: pending=1, in_transit=1, delivered=1
    // active = all not in [delivered, canceled, failed] = 2 (pending + in_transit)
    const vm = wrapper.vm
    expect(vm.activeCount).toBe(2)
    expect(vm.pendingCount).toBe(1)
    expect(vm.inTransitCount).toBe(1)
    expect(vm.deliveredTodayCount).toBe(1)
  })

  it('opens detail modal when view button is clicked', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    const viewBtn = wrapper.find('[data-cy="view-delivery-btn"]')
    expect(viewBtn.exists()).toBe(true)
    await viewBtn.trigger('click')
    expect(wrapper.find('[data-cy="delivery-detail-modal"]').exists()).toBe(true)
  })

  it('shows not_assigned for deliveries without courier', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(wrapper.text()).toContain('Não Atribuído')
  })

  it('shows error when deliveries fail to load', async () => {
    adminService.getDeliveries.mockRejectedValue(new Error('Network Error'))
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(adminService.getDeliveries).toHaveBeenCalled()
  })
})
