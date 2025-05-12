import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import OrderCard from '../OrderCard.vue'

describe('OrderCard', () => {
  const order = {
    id: 1,
    customer: 'John Doe',
    items: ['Pizza', 'Salad'],
    status: 'preparing',
    preparationTime: 15
  }

  it('renders order details', () => {
    const wrapper = mount(OrderCard, {
      props: { order }
    })
    expect(wrapper.text()).toContain('Order #1')
    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('Pizza, Salad')
  })

  it('displays status badge', () => {
    const wrapper = mount(OrderCard, {
      props: { order }
    })
    expect(wrapper.find('[data-test="status-badge"]').text()).toBe('PREPARING')
  })

  it('shows preparation time', () => {
    const wrapper = mount(OrderCard, {
      props: { order }
    })
    expect(wrapper.find('[data-test="prep-time"]').text()).toContain('15 min')
  })

  it('emits status update event', async () => {
    const wrapper = mount(OrderCard, {
      props: { order }
    })
    await wrapper.find('[data-test="ready-btn"]').trigger('click')
    expect(wrapper.emitted('status-update')).toBeTruthy()
    expect(wrapper.emitted('status-update')[0]).toEqual([1, 'ready'])
  })
})
