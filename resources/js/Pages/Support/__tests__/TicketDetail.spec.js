import { mount } from '@vue/test-utils'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import TicketDetail from '../TicketDetail.vue'
import supportService from '@/services/support'

vi.mock('@/services/support', () => ({
  default: {
    getTicket: vi.fn(),
    closeTicket: vi.fn()
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

vi.mock('@/stores/notification', () => ({
  useNotificationStore: () => ({
    success: vi.fn(),
    error: vi.fn()
  })
}))

describe('Support TicketDetail', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    supportService.getTicket.mockResolvedValue({ 
      data: { 
        id: 1, 
        subject: 'Test Ticket', 
        status: 'open', 
        category: 'account', 
        priority: 'medium',
        messages: [{ user_name: 'User', body: 'Help!', created_at: '2023-01-01' }]
      } 
    })
  })

  it('renders ticket details', async () => {
    const wrapper = mount(TicketDetail, {
      props: { id: '1' },
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          'v-row': { template: '<div><slot /></div>' },
          'v-col': { template: '<div><slot /></div>' },
          'v-card': { template: '<div><slot /></div>' },
          'v-card-title': { template: '<div><slot /></div>' },
          'v-card-text': { template: '<div><slot /></div>' },
          'v-list': { template: '<div><slot /></div>' },
          'v-list-item': { template: '<div><slot /></div>' },
          'v-chip': { template: '<div><slot /></div>' },
          'v-btn': { template: '<button><slot /></button>' },
          'v-progress-circular': true
        }
      }
    })
    
    await vi.waitFor(() => expect(wrapper.vm.loading).toBe(false))
    expect(wrapper.text()).toContain('Test Ticket')
  })

  it('calls closeTicket on confirmation', async () => {
    const wrapper = mount(TicketDetail, {
      props: { id: '1' },
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          'v-row': { template: '<div><slot /></div>' },
          'v-col': { template: '<div><slot /></div>' },
          'v-card': { template: '<div><slot /></div>' },
          'v-card-title': { template: '<div><slot /></div>' },
          'v-card-text': { template: '<div><slot /></div>' },
          'v-card-actions': { template: '<div><slot /></div>' },
          'v-list': { template: '<div><slot /></div>' },
          'v-list-item': { template: '<div><slot /></div>' },
          'v-chip': { template: '<div><slot /></div>' },
          'v-btn': { template: '<button @click="$emit(\'click\')"><slot /></button>' },
          'v-dialog': { template: '<div><slot /></div>' },
          'v-spacer': true,
          'v-progress-circular': true
        }
      }
    })

    await vi.waitFor(() => expect(wrapper.vm.loading).toBe(false))
    
    // We need to trigger the reply-btn or something that is definitely there
    expect(wrapper.find('[data-cy="close-btn"]').exists()).toBe(true)
    await wrapper.find('[data-cy="close-btn"]').trigger('click')
    expect(wrapper.vm.showCloseDialog).toBe(true)
    
    await wrapper.vm.closeTicket()
    expect(supportService.closeTicket).toHaveBeenCalled()
  })
})
