import { mount } from '@vue/test-utils'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import TicketCreate from '../TicketCreate.vue'
import supportService from '@/services/support'

vi.mock('@/services/support', () => ({
  default: {
    createTicket: vi.fn()
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

describe('Support TicketCreate', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders form correctly', () => {
    const wrapper = mount(TicketCreate, {
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          'v-card': { template: '<div><slot /></div>' },
          'v-form': { template: '<form><slot /></form>' },
          'v-text-field': true,
          'v-select': true,
          'v-textarea': true,
          'v-file-input': true,
          'v-btn': true
        }
      }
    })
    expect(wrapper.find('[data-cy="support-ticket-create"]').exists()).toBe(true)
  })

  it('calls createTicket on submit', async () => {
    supportService.createTicket.mockResolvedValue({ success: true })
    const wrapper = mount(TicketCreate, {
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          'v-card': { template: '<div><slot /></div>' },
          'v-form': { template: '<form @submit.prevent="$emit(\'submit\')"><slot /></form>' },
          'v-text-field': true,
          'v-select': true,
          'v-textarea': true,
          'v-file-input': true,
          'v-btn': true
        }
      }
    })

    wrapper.vm.isValid = true
    await wrapper.vm.submit() // Call submit directly to avoid event issues in stubs
    expect(supportService.createTicket).toHaveBeenCalled()
  })
})
