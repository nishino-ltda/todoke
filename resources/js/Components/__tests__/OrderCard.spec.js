import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import OrderCard from '../OrderCard.vue'

describe('OrderCard', () => {
  const baseOrder = {
    id: 1,
    customer: 'John Doe',
    items: ['Pizza', 'Salad'],
    status: 'preparing',
    preparationTime: 15
  }

  it('renders order details', () => {
    const wrapper = mount(OrderCard, {
      props: { order: baseOrder }
    })
    expect(wrapper.text()).toContain('Order #1')
    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('Pizza, Salad')
  })

  it('displays status badge', () => {
    const wrapper = mount(OrderCard, {
      props: { order: baseOrder }
    })
    expect(wrapper.find('[data-test="status-badge"]').text()).toBe('PREPARING')
  })

  it('shows preparation time', () => {
    const wrapper = mount(OrderCard, {
      props: { order: baseOrder }
    })
    expect(wrapper.find('[data-test="prep-time"]').text()).toContain('15 min')
  })

  it('emits status update event (preparing → ready)', async () => {
    const wrapper = mount(OrderCard, {
      props: { order: baseOrder }
    })
    await wrapper.find('[data-test="ready-btn"]').trigger('click')
    expect(wrapper.emitted('status-update')).toBeTruthy()
    expect(wrapper.emitted('status-update')[0]).toEqual([1, 'ready'])
  })

  it('emits status update event (ready → delivered)', async () => {
    const wrapper = mount(OrderCard, {
      props: { order: { ...baseOrder, status: 'ready' } }
    })
    await wrapper.find('[data-test="delivered-btn"]').trigger('click')
    expect(wrapper.emitted('status-update')).toBeTruthy()
    expect(wrapper.emitted('status-update')[0]).toEqual([1, 'delivered'])
  })

  it('handles missing preparation time', () => {
    const wrapper = mount(OrderCard, {
      props: { order: { ...baseOrder, preparationTime: null } }
    })
    expect(wrapper.find('[data-test="prep-time"]').exists()).toBe(false)
  })

  it('handles empty items list', () => {
    const wrapper = mount(OrderCard, {
      props: { order: { ...baseOrder, items: [] } }
    })
    expect(wrapper.find('[data-test="items-list"]').text()).toContain('No items')
  })

  it('only shows valid status buttons', () => {
    const wrapper = mount(OrderCard, {
      props: { order: { ...baseOrder, status: 'delivered' } }
    })
    expect(wrapper.find('[data-test="ready-btn"]').exists()).toBe(false)
    expect(wrapper.find('[data-test="delivered-btn"]').exists()).toBe(false)
  })

  it('shows error for invalid status', () => {
    const wrapper = mount(OrderCard, {
      props: { order: { ...baseOrder, status: 'invalid' } }
    })
    expect(wrapper.find('[data-test="status-error"]').exists()).toBe(true)
  })
})
