import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import OrderList from '../OrderList.vue'

describe('OrderList', () => {
  it('renders properly', () => {
    const wrapper = mount(OrderList)
    expect(wrapper.exists()).toBe(true)
  })

  it('displays filtered orders', async () => {
    const wrapper = mount(OrderList, {
      props: {
        orders: [
          { id: 1, status: 'preparing' },
          { id: 2, status: 'ready' }
        ],
        filter: 'preparing'
      }
    })
    await wrapper.vm.$nextTick()
    expect(wrapper.findAll('[data-test="order-item"]')).toHaveLength(1)
    expect(wrapper.find('[data-test="order-item"]').text()).toContain('Order #1')
  })

  it('shows empty state when no orders', () => {
    const wrapper = mount(OrderList, {
      props: {
        orders: [],
        filter: 'all'
      }
    })
    expect(wrapper.find('[data-test="empty-state"]').exists()).toBe(true)
  })
})
