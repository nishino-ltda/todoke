import { mount } from '@vue/test-utils'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import Tickets from '../Tickets.vue'
import supportService from '@/services/support'

vi.mock('@/services/support', () => ({
  default: {
    getTickets: vi.fn()
  }
}))

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key) => key
  })
}))

vi.mock('@inertiajs/vue3', () => ({
  router: {
    visit: vi.fn()
  }
}))

describe('Support Tickets', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    supportService.getTickets.mockResolvedValue({ data: [] })
  })

  it('renders correctly', () => {
    const wrapper = mount(Tickets, {
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          DataTable: { template: '<div data-cy="tickets-table"></div>' },
          'v-btn': true
        }
      }
    })
    expect(wrapper.find('[data-cy="support-tickets-index"]').exists()).toBe(true)
    expect(wrapper.find('[data-cy="tickets-table"]').exists()).toBe(true)
  })

  it('filters tickets by status', async () => {
    const wrapper = mount(Tickets, {
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          DataTable: true,
          'v-btn': true
        }
      }
    })
    
    wrapper.vm.tickets = [
      { id: 1, status: 'open' },
      { id: 2, status: 'resolved' }
    ]
    
    wrapper.vm.selectedStatus = 'open'
    expect(wrapper.vm.filteredTickets.length).toBe(1)
    expect(wrapper.vm.filteredTickets[0].id).toBe(1)
  })
})
