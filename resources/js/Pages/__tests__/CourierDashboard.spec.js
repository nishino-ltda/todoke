import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import Dashboard from '../Courier/Dashboard.vue'
import deliveryService from '../../services/delivery'
import { createPinia, setActivePinia } from 'pinia'

// Mock deliveryService
vi.mock('../../services/delivery', () => ({
  default: {
    getMyDeliveries: vi.fn(),
    updateStatus: vi.fn()
  }
}))

// Mock Inertia
vi.mock('@inertiajs/vue3', () => ({
  usePage: () => ({ url: '/courier' }),
  router: { visit: vi.fn(), post: vi.fn() }
}))

const stubs = {
  CourierLayout: { template: '<div class="courier-layout"><slot /></div>' },
  VApp: { template: '<div><slot /></div>' },
  VMain: { template: '<div><slot /></div>' },
  VContainer: { template: '<div><slot /></div>' },
  VCard: { template: '<div class="v-card"><slot /></div>' },
  VCardText: { template: '<div><slot /></div>' },
  VBtn: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
  VSwitch: { template: '<input type="checkbox" v-model="internalValue" />', props: ['modelValue'], data() { return { internalValue: this.modelValue } } },
  VIcon: { template: '<i></i>' },
  VChip: { template: '<span><slot /></span>' },
  VProgressCircular: { template: '<div></div>' },
  VFadeTransition: { template: '<div><slot /></div>' }
}

describe('Courier Dashboard', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    
    deliveryService.getMyDeliveries.mockResolvedValue({
      data: [
        { id: 1, status: 'pending', origin_address: 'Start', destination_address: 'End', fee: 5, distance: 2, restaurant_name: 'Pizza Place' }
      ]
    })
  })

  it('fetches deliveries on mount', async () => {
    const wrapper = mount(Dashboard, { global: { stubs } })
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(deliveryService.getMyDeliveries).toHaveBeenCalled()
  })

  it('can accept an available delivery', async () => {
    deliveryService.updateStatus.mockResolvedValue({ success: true })
    const wrapper = mount(Dashboard, { global: { stubs } })
    
    await new Promise(resolve => setTimeout(resolve, 0))
    const acceptBtn = wrapper.find('[data-cy="accept-delivery-btn"]')
    await acceptBtn.trigger('click')
    
    expect(deliveryService.updateStatus).toHaveBeenCalledWith(1, 'accepted')
  })

  it('shows active delivery status if one exists', async () => {
    deliveryService.getMyDeliveries.mockResolvedValue({
      data: [
        { id: 2, status: 'accepted', origin_address: 'Start', destination_address: 'End', fee: 5, distance: 2, restaurant_name: 'Pizza Place' }
      ]
    })
    
    const wrapper = mount(Dashboard, { global: { stubs } })
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.find('.active-delivery').exists()).toBe(true)
    expect(wrapper.text()).toContain('Arrived at Restaurant') // Next status label
  })
})
