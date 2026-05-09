import { mount } from '@vue/test-utils'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import Dashboard from '../Dashboard.vue'
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

describe('Support Dashboard', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    supportService.getTickets.mockResolvedValue({ 
      data: [
        { id: 1, subject: 'Issue 1', status: 'open', created_at: '2023-01-01' },
        { id: 2, subject: 'Issue 2', status: 'resolved', created_at: '2023-01-02' }
      ] 
    })
  })

  it('renders correctly', () => {
    const wrapper = mount(Dashboard, {
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          'v-row': true,
          'v-col': true,
          'v-card': true,
          'v-card-title': true,
          'v-btn': true,
          'v-list': true,
          'v-list-item': true,
          'v-list-item-title': true,
          'v-chip': true
        }
      }
    })
    expect(wrapper.find('[data-cy="support-dashboard"]').exists()).toBe(true)
  })

  it('calculates stats correctly', async () => {
    const wrapper = mount(Dashboard, {
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          'v-row': true,
          'v-col': true,
          'v-card': { template: '<div><slot /></div>' },
          'v-btn': true,
          'v-list': true,
          'v-list-item': true,
          'v-chip': true
        }
      }
    })
    
    await vi.waitFor(() => expect(wrapper.vm.stats.open).toBe(1))
    expect(wrapper.vm.stats.resolved).toBe(1)
  })
})
