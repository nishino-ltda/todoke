import { describe, it, expect, vi } from 'vitest'
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

  it('shows error state when loading fails', async () => {
    const wrapper = mount(PartnerDashboard, {
      global: {
        mocks: {
          $store: {
            state: {
              partner: {
                error: 'Failed to load orders',
                orders: []
              }
            }
          }
        }
      }
    })
    await wrapper.vm.$nextTick()
    expect(wrapper.find('[data-test="error-message"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="error-message"]').text()).toContain('Failed to load orders')
  })

  it('allows retrying after error', async () => {
    const mockRetry = vi.fn()
    const wrapper = mount(PartnerDashboard, {
      global: {
        mocks: {
          $store: {
            state: {
              partner: {
                error: 'Failed to load orders',
                orders: []
              }
            },
            dispatch: mockRetry
          }
        }
      }
    })
    await wrapper.vm.$nextTick()
    await wrapper.find('[data-test="retry-button"]').trigger('click')
    expect(mockRetry).toHaveBeenCalledWith('partner/loadOrders')
  })
})
