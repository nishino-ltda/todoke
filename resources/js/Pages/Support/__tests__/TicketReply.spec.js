import { mount } from '@vue/test-utils'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import TicketReply from '../TicketReply.vue'
import supportService from '@/services/support'

vi.mock('@/services/support', () => ({
  default: {
    addReply: vi.fn()
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

describe('Support TicketReply', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders form correctly', () => {
    const wrapper = mount(TicketReply, {
      props: { id: '1' },
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          'v-card': { template: '<div><slot /></div>' },
          'v-form': true,
          'v-textarea': true,
          'v-file-input': true,
          'v-btn': true
        }
      }
    })
    expect(wrapper.find('[data-cy="support-ticket-reply"]').exists()).toBe(true)
  })

  it('calls addReply on submit', async () => {
    supportService.addReply.mockResolvedValue({ success: true })
    const wrapper = mount(TicketReply, {
      props: { id: '1' },
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          'v-card': { template: '<div><slot /></div>' },
          'v-form': { template: '<form @submit.prevent="$emit(\'submit\')"><slot /></form>' },
          'v-textarea': true,
          'v-file-input': true,
          'v-btn': true
        }
      }
    })

    wrapper.vm.isValid = true
    await wrapper.vm.submit()
    expect(supportService.addReply).toHaveBeenCalled()
  })
})
