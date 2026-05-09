import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import Dashboard from '../Partner/Dashboard.vue'
import partnerService from '../../services/partner'
import { createPinia, setActivePinia } from 'pinia'

// Mock partnerService
vi.mock('../../services/partner', () => ({
  default: {
    getDashboardStats: vi.fn(),
    getOrders: vi.fn(),
    updateOrderStatus: vi.fn()
  }
}))

// Mock Inertia usePage
vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({
    url: '/partner',
    props: {
      auth: { user: { name: 'Partner User' } }
    }
  }),
  router: {
    visit: vi.fn()
  }
}))

// Mock Vuetify and generic components
const stubs = {
  PartnerLayout: {
    template: '<div class="partner-layout"><slot /></div>'
  },
  MetricsWidget: {
    template: '<div class="metrics-widget">{{ title }}: {{ value }}</div>',
    props: ['title', 'value', 'icon', 'color']
  },
  DataTable: {
    template: '<div class="data-table"><slot name="item.status" :item="{status: \'pending\'}" /><slot name="item.actions" :item="{id: 1, status: \'pending\'}" /></div>',
    props: ['items', 'headers', 'loading']
  },
  AppModal: {
    template: '<div class="app-modal" v-if="modelValue"><slot /></div>',
    props: ['modelValue', 'title']
  },
  VRow: { template: '<div class="v-row"><slot /></div>' },
  VCol: { template: '<div class="v-col"><slot /></div>' },
  VCard: { template: '<div class="v-card"><slot /></div>' },
  VCardText: { template: '<div class="v-card-text"><slot /></div>' },
  VIcon: { template: '<i class="v-icon"></i>' },
  VChip: { template: '<span class="v-chip"><slot /></span>' },
  VBtn: { template: '<button class="v-btn" @click="$emit(\'click\')"><slot /></button>' },
  VDivider: { template: '<hr />' },
  VList: { template: '<div class="v-list"><slot /></div>' },
  VListItem: { template: '<div class="v-list-item"><slot /></div>' },
  VListItemTitle: { template: '<div class="v-list-item-title"><slot /></div>' }
}

describe('Partner Dashboard', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    
    partnerService.getDashboardStats.mockResolvedValue({
      data: {
        new_orders: 5,
        preparing: 2,
        today_revenue: 150.50,
        completed: 10
      }
    })
    
    partnerService.getOrders.mockResolvedValue({
      data: [
        { id: 1, customer_name: 'John Doe', total: 25.50, status: 'pending', items: [] }
      ]
    })
  })

  it('fetches dashboard data on mount', async () => {
    const wrapper = mount(Dashboard, {
      global: { stubs }
    })
    
    // Wait for async operations
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(partnerService.getDashboardStats).toHaveBeenCalled()
    expect(partnerService.getOrders).toHaveBeenCalled()
  })

  it('displays metrics from service', async () => {
    const wrapper = mount(Dashboard, {
      global: { stubs }
    })
    
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.text()).toContain('5') // New orders
    expect(wrapper.text()).toContain('$150.50') // Revenue
  })

  it('can accept a pending order', async () => {
    partnerService.updateOrderStatus.mockResolvedValue({ success: true })
    
    const wrapper = mount(Dashboard, {
      global: { stubs }
    })
    
    await new Promise(resolve => setTimeout(resolve, 0))
    
    const acceptBtn = wrapper.find('[data-cy="accept-order-btn"]')
    await acceptBtn.trigger('click')
    
    expect(partnerService.updateOrderStatus).toHaveBeenCalledWith(1, 'preparing')
  })
})
