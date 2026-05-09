import { mount } from '@vue/test-utils'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import Faq from '../Faq.vue'
import supportService from '@/services/support'

vi.mock('@/services/support', () => ({
  default: {
    getFaqs: vi.fn()
  }
}))

vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key) => key
  })
}))

describe('Support FAQ', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    supportService.getFaqs.mockResolvedValue({ 
      data: [
        { question: 'Q1', answer: 'A1' },
        { question: 'Q2', answer: 'A2' }
      ] 
    })
  })

  it('renders correctly', async () => {
    const wrapper = mount(Faq, {
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          'v-text-field': true,
          'v-expansion-panels': true,
          'v-expansion-panel': true,
          'v-progress-circular': true
        }
      }
    })
    
    await vi.waitFor(() => expect(wrapper.find('[data-cy="support-faq"]').exists()).toBe(true))
  })

  it('filters faqs by search', async () => {
    const wrapper = mount(Faq, {
      global: {
        stubs: {
          SupportLayout: { template: '<div><slot /></div>' },
          'v-text-field': true,
          'v-expansion-panels': true,
          'v-expansion-panel': true
        }
      }
    })
    
    wrapper.vm.faqs = [
      { question: 'Delivery', answer: 'A1' },
      { question: 'Payment', answer: 'A2' }
    ]
    
    wrapper.vm.search = 'delivery'
    expect(wrapper.vm.filteredFaqs.length).toBe(1)
    expect(wrapper.vm.filteredFaqs[0].question).toBe('Delivery')
  })
})
