import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import Dashboard from '../Courier/Dashboard.vue'
import deliveryService from '../../services/delivery'
import { createPinia, setActivePinia } from 'pinia'
import { createI18n } from 'vue-i18n'

// Mock the full deliveryService with all new methods
vi.mock('../../services/delivery', () => ({
  default: {
    getAvailableDeliveries: vi.fn(),
    getMyActiveDelivery: vi.fn(),
    acceptDelivery: vi.fn(),
    rejectDelivery: vi.fn(),
    updateDeliveryStatus: vi.fn(),
    getDeliveryDetails: vi.fn(),
    // Legacy
    getMyDeliveries: vi.fn(),
    updateStatus: vi.fn(),
    getDetails: vi.fn(),
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
        accepted: 'Aceita',
        collected: 'Coletada',
        in_transit: 'Em Trânsito',
        delivered: 'Entregue',
      },
      activeDelivery: {
        title: 'ENTREGA ATIVA',
        id: 'ID',
        pickup: 'RETIRADA',
        dropoff: 'ENTREGA',
        noDelivery: 'Nenhuma entrega ativa',
      },
      availableDeliveries: {
        title: 'ENTREGAS DISPONÍVEIS',
        distance: 'a {distance} km',
        accept: 'Aceitar Pedido',
        reject: 'Recusar',
        empty: 'Procurando entregas na sua área...',
        unknown_partner: 'Parceiro Desconhecido',
      },
      actions: {
        goOnline: 'Fique online para começar a receber pedidos de entrega.',
        updateStatus: 'Atualizar Status',
      },
      stages: {
        arrived: 'Cheguei no Restaurante',
        picked_up: 'Pedido Coletado',
        delivered: 'Marcar como Entregue',
        next: 'Próxima Etapa',
      },
      notifications: {
        load_failed: 'Falha ao carregar entregas',
        accept_success: 'Entrega aceita!',
        accept_failed: 'Falha ao aceitar entrega',
        reject_success: 'Entrega recusada',
        reject_failed: 'Falha ao recusar entrega',
        update_success: 'Status atualizado para {status}',
        update_failed: 'Falha ao atualizar status',
      },
      map: {
        loading: 'Carregando mapa...',
        error: 'Falha ao calcular rota',
        origin: 'Retirada',
        destination: 'Entrega',
        current: 'Sua Localização',
        distance: 'Distância: {distance} km',
        estimatedTime: 'Tempo Estimado: {time} min',
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
        accepted: 'Accepted',
        collected: 'Collected',
        in_transit: 'In Transit',
        delivered: 'Delivered',
      },
      activeDelivery: {
        title: 'ACTIVE DELIVERY',
        id: 'ID',
        pickup: 'PICKUP',
        dropoff: 'DROP OFF',
        noDelivery: 'No active delivery',
      },
      availableDeliveries: {
        title: 'AVAILABLE REQUESTS',
        distance: '{distance} km away',
        accept: 'Accept Order',
        reject: 'Reject',
        empty: 'Looking for deliveries in your area...',
        unknown_partner: 'Unknown Partner',
      },
      actions: {
        goOnline: 'Go online to start receiving delivery requests.',
        updateStatus: 'Update Status',
      },
      stages: {
        arrived: 'Arrived at Restaurant',
        picked_up: 'Order Picked Up',
        delivered: 'Mark as Delivered',
        next: 'Next Stage',
      },
      notifications: {
        load_failed: 'Failed to load deliveries',
        accept_success: 'Delivery accepted!',
        accept_failed: 'Failed to accept delivery',
        reject_success: 'Delivery rejected',
        reject_failed: 'Failed to reject delivery',
        update_success: 'Status updated to {status}',
        update_failed: 'Failed to update status',
      },
      map: {
        loading: 'Loading map...',
        error: 'Failed to calculate route',
        origin: 'Pickup',
        destination: 'Drop-off',
        current: 'Your Location',
        distance: 'Distance: {distance} km',
        estimatedTime: 'Estimated Time: {time} mins',
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
  VBtn: {
    template: '<button :data-cy="$attrs[\'data-cy\']" @click="$emit(\'click\')"><slot /></button>',
    props: ['loading', 'disabled']
  },
  VSwitch: {
    template: '<input type="checkbox" :data-cy="$attrs[\'data-cy\']" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
    props: ['modelValue']
  },
  VIcon: { template: '<i></i>' },
  VChip: { template: '<span :data-cy="$attrs[\'data-cy\']"><slot /></span>' },
  VProgressCircular: { template: '<div></div>' },
  VFadeTransition: { template: '<div><slot /></div>' },
  VAlert: { template: '<div :data-cy="$attrs[\'data-cy\']" class="v-alert"><slot /></div>' },
  DeliveryMap: { template: '<div data-cy="delivery-map"></div>' },
}

const pendingDelivery = {
  id: 1,
  status: 'pending',
  origin_address: 'Start',
  destination_address: 'End',
  fee: 5.5,
  value: 5.5,
  distance: 2,
  restaurant_name: 'Pizza Place',
}

const acceptedDelivery = {
  id: 2,
  status: 'accepted',
  origin_address: 'Start',
  destination_address: 'End',
  fee: 5,
  value: 5,
  distance: 2,
  restaurant_name: 'Pizza Place',
  origin_lat: -23.5,
  origin_lng: -46.6,
  destination_lat: -23.6,
  destination_lng: -46.7,
}

function createWrapper(locale = 'pt-BR') {
  const i18n = createI18n({ legacy: false, locale, messages })
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

    // Default: return pending deliveries (available), no active delivery
    deliveryService.getAvailableDeliveries.mockResolvedValue({
      data: { deliveries: [pendingDelivery], total: 1, per_page: 15, current_page: 1, last_page: 1 }
    })
    deliveryService.getMyActiveDelivery.mockResolvedValue({
      data: { deliveries: [], total: 0, per_page: 15, current_page: 1, last_page: 1 }
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

  it('translates dynamic status labels for active delivery', async () => {
    // Override: no pending, one active
    deliveryService.getAvailableDeliveries.mockResolvedValue({
      data: { deliveries: [], total: 0, per_page: 15, current_page: 1, last_page: 1 }
    })
    deliveryService.getMyActiveDelivery.mockResolvedValue({
      data: { deliveries: [acceptedDelivery], total: 1, per_page: 15, current_page: 1, last_page: 1 }
    })

    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(wrapper.find('.active-delivery').exists()).toBe(true)
    expect(wrapper.text()).toContain('Aceita')
    expect(wrapper.text()).toContain('Cheguei no Restaurante')
  })

  it('accept button shows translated label and calls acceptDelivery', async () => {
    deliveryService.acceptDelivery.mockResolvedValue({
      data: { id: 1, status: 'accepted' }
    })
    // After accept, refresh returns empty available, one active
    deliveryService.getAvailableDeliveries.mockResolvedValueOnce({
      data: { deliveries: [pendingDelivery], total: 1, per_page: 15, current_page: 1, last_page: 1 }
    })

    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))

    const acceptBtn = wrapper.find('[data-cy="accept-delivery-btn"]')
    expect(acceptBtn.exists()).toBe(true)
    expect(acceptBtn.text()).toBe('Aceitar Pedido')

    await acceptBtn.trigger('click')
    expect(deliveryService.acceptDelivery).toHaveBeenCalledWith(1)
  })

  it('shows offline state when toggle is off', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))

    // Toggle offline
    const toggle = wrapper.find('[data-cy="availability-toggle"]')
    await toggle.setValue(false)

    expect(wrapper.find('[data-cy="offline-state"]').exists()).toBe(true)
    expect(wrapper.find('[data-cy="available-deliveries-section"]').exists()).toBe(false)
  })
})
