import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import OrderList from '../OrderList.vue'

describe('OrderList', () => {
  const orders = [
    { id: 1, status: 'preparing' },
    { id: 2, status: 'ready' },
    { id: 3, status: 'delivered' },
    { id: 4, status: 'preparing' }
  ]

  it('renders properly', () => {
    const wrapper = mount(OrderList)
    expect(wrapper.exists()).toBe(true)
  })

  it('displays filtered orders (preparing)', async () => {
    const wrapper = mount(OrderList, {
      props: { orders, filter: 'preparing' }
    })
    await wrapper.vm.$nextTick()
    const items = wrapper.findAll('[data-test="order-item"]')
    expect(items).toHaveLength(2)
    expect(items[0].text()).toContain('Order #1')
    expect(items[1].text()).toContain('Order #4')
  })

  it('displays filtered orders (ready)', async () => {
    const wrapper = mount(OrderList, {
      props: { orders, filter: 'ready' }
    })
    await wrapper.vm.$nextTick()
    const items = wrapper.findAll('[data-test="order-item"]')
    expect(items).toHaveLength(1)
    expect(items[0].text()).toContain('Order #2')
  })

  it('shows empty state when no orders match filter', async () => {
    const wrapper = mount(OrderList, {
      props: { orders, filter: 'cancelled' }
    })
    await wrapper.vm.$nextTick()
    expect(wrapper.find('[data-test="empty-filtered-state"]').exists()).toBe(true)
  })

  it('shows all orders when filter is "all"', async () => {
    const wrapper = mount(OrderList, {
      props: { orders, filter: 'all' }
    })
    await wrapper.vm.$nextTick()
    expect(wrapper.findAll('[data-test="order-item"]')).toHaveLength(4)
  })

  it('shows empty state when no orders', () => {
    const wrapper = mount(OrderList, {
      props: { orders: [], filter: 'all' }
    })
    expect(wrapper.find('[data-test="empty-state"]').exists()).toBe(true)
  })

  it('updates filtered results when props change', async () => {
    const wrapper = mount(OrderList, {
      props: { orders, filter: 'preparing' }
    })
    await wrapper.vm.$nextTick()
    expect(wrapper.findAll('[data-test="order-item"]')).toHaveLength(2)

    await wrapper.setProps({ filter: 'ready' })
    expect(wrapper.findAll('[data-test="order-item"]')).toHaveLength(1)
  })
})
