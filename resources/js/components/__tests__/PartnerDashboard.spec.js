import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import PartnerDashboard from '../PartnerDashboard.vue'

describe('PartnerDashboard', () => {
  it('renders properly', () => {
    const wrapper = mount(PartnerDashboard)
    expect(wrapper.exists()).toBe(true)
  })

  it('displays loading state initially', () => {
    const wrapper = mount(PartnerDashboard)
    expect(wrapper.find('[data-test="loading"]').exists()).toBe(true)
  })

  it('shows orders when loaded', async () => {
    const wrapper = mount(PartnerDashboard, {
      global: {
        mocks: {
          $store: {
            state: {
              partner: {
                orders: [
                  { id: 1, status: 'preparing' },
                  { id: 2, status: 'ready' }
                ]
              }
            }
          }
        }
      }
    })
    await wrapper.vm.$nextTick()
    expect(wrapper.findAll('[data-test="order-card"]')).toHaveLength(2)
  })
})
