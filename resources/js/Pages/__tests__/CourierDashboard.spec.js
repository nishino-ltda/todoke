import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import Dashboard from '../Courier/Dashboard.vue'
import deliveryService from '../../services/delivery'
import { createPinia, setActivePinia } from 'pinia'
import { createI18n } from 'vue-i18n'

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
  router: { visit: vi.fn(), post: vi.fn() },
  Head: { template: '<div><slot /></div>' }
}))

const messages = {
  'pt-BR': {
    locale: 'pt-BR',
    courier: {
      status: { 
        title: 'STATUS ATUAL', 
        online: 'Online', 
        offline: 'Offline', 
        accepted: 'Aceita' 
      },
      activeDelivery: { 
        title: 'ENTREGA ATIVA', 
        id: 'ID', 
        pickup: 'RETIRADA', 
        dropoff: 'ENTREGA' 
      },
      availableDeliveries: { 
        title: 'ENTREGAS DISPONÍVEIS',
        distance: 'a {distance} km',
        accept: 'Aceitar Pedido',
        empty: 'Procurando entregas na sua área...'
      },
      actions: {
        goOnline: 'Fique online para começar a receber pedidos de entrega.'
      },
      stages: { 
        arrived: 'Cheguei no Restaurante' 
      },
      notifications: {
        load_failed: 'Falha ao carregar entregas',
        accept_success: 'Entrega aceita!',
        update_success: 'Status atualizado para {status}'
      }
    }
  },
  'en': {
    locale: 'en',
    courier: {
      status: { 
        title: 'CURRENT STATUS', 
        online: 'Online', 
        offline: 'Offline', 
        accepted: 'Accepted' 
      },
      activeDelivery: { 
        title: 'ACTIVE DELIVERY', 
        id: 'ID', 
        pickup: 'PICKUP', 
        dropoff: 'DROP OFF' 
      },
      availableDeliveries: { 
        title: 'AVAILABLE REQUESTS',
        distance: '{distance} km away',
        accept: 'Accept Order',
        empty: 'Looking for deliveries in your area...'
      },
      actions: {
        goOnline: 'Go online to start receiving delivery requests.'
      },
      stages: { 
        arrived: 'Arrived at Restaurant' 
      },
      notifications: {
        load_failed: 'Failed to load deliveries',
        accept_success: 'Delivery accepted!',
        update_success: 'Status updated to {status}'
      }
    }
  }
}

const stubs = {
  CourierLayout: { template: '<div class="courier-layout"><slot /></div>' },
  VApp: { template: '<div><slot /></div>' },
  VMain: { template: '<div><slot /></div>' },
  VContainer: { template: '<div><slot /></div>' },
  VCard: { template: '<div class="v-card"><slot /></div>' },
  VCardText: { template: '<div><slot /></div>' },
  VBtn: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
  VSwitch: { template: '<input type="checkbox" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />', props: ['modelValue'] },
  VIcon: { template: '<i></i>' },
  VChip: { template: '<span><slot /></span>' },
  VProgressCircular: { template: '<div></div>' },
  VFadeTransition: { template: '<div><slot /></div>' }
}

function createWrapper(locale = 'pt-BR') {
  const i18n = createI18n({
    legacy: false,
    locale,
    messages
  })
  return mount(Dashboard, {
    global: {
      plugins: [i18n],
      stubs
    }
  })
}

describe('Courier Dashboard i18n', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    
    deliveryService.getMyDeliveries.mockResolvedValue({
      data: [
        { id: 1, status: 'pending', origin_address: 'Start', destination_address: 'End', fee: 5.5, distance: 2, restaurant_name: 'Pizza Place' }
      ]
    })
  })

  it('renders in pt-BR by default', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.text()).toContain('STATUS ATUAL')
    expect(wrapper.text()).toContain('ENTREGAS DISPONÍVEIS')
    expect(wrapper.text()).toContain('a 2 km')
    expect(wrapper.text().replace(/\u00a0/g, ' ')).toContain('R$ 5,50')
  })

  it('renders in en when locale is switched', async () => {
    const wrapper = createWrapper('en')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.text()).toContain('CURRENT STATUS')
    expect(wrapper.text()).toContain('AVAILABLE REQUESTS')
    expect(wrapper.text()).toContain('2 km away')
    expect(wrapper.text().replace(/\u00a0/g, ' ')).toContain('$5.50')
  })

  it('translates dynamic status labels', async () => {
    deliveryService.getMyDeliveries.mockResolvedValue({
      data: [
        { id: 2, status: 'accepted', origin_address: 'Start', destination_address: 'End', fee: 5, distance: 2, restaurant_name: 'Pizza Place' }
      ]
    })
    
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.find('.active-delivery').exists()).toBe(true)
    expect(wrapper.text()).toContain('Aceita')
    expect(wrapper.text()).toContain('Cheguei no Restaurante')
  })

  it('can accept a delivery with translated button', async () => {
    deliveryService.updateStatus.mockResolvedValue({ success: true })
    const wrapper = createWrapper('pt-BR')
    
    await new Promise(resolve => setTimeout(resolve, 0))
    const acceptBtn = wrapper.find('[data-cy="accept-delivery-btn"]')
    expect(acceptBtn.text()).toBe('Aceitar Pedido')
    
    await acceptBtn.trigger('click')
    expect(deliveryService.updateStatus).toHaveBeenCalledWith(1, 'accepted')
  })
})
