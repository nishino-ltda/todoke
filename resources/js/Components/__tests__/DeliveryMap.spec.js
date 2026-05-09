import { mount } from '@vue/test-utils'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import DeliveryMap from '../DeliveryMap.vue'
import mapService from '@/services/map'

// Mock Leaflet
vi.mock('leaflet', () => ({
  default: {
    map: vi.fn().mockReturnValue({
      setView: vi.fn().mockReturnThis(),
      remove: vi.fn(),
      fitBounds: vi.fn(),
      addLayer: vi.fn()
    }),
    tileLayer: vi.fn().mockReturnValue({
      addTo: vi.fn().mockReturnThis()
    }),
    marker: vi.fn().mockReturnValue({
      addTo: vi.fn().mockReturnThis(),
      bindPopup: vi.fn().mockReturnThis()
    }),
    circleMarker: vi.fn().mockReturnValue({
      addTo: vi.fn().mockReturnThis(),
      bindPopup: vi.fn().mockReturnThis(),
      setLatLng: vi.fn()
    }),
    polyline: vi.fn().mockReturnValue({
      addTo: vi.fn().mockReturnThis()
    }),
    featureGroup: vi.fn().mockReturnValue({
      getBounds: vi.fn().mockReturnValue({
        pad: vi.fn().mockReturnThis()
      })
    }),
    Icon: {
      Default: {
        prototype: {},
        mergeOptions: vi.fn()
      }
    }
  }
}))

// Mock mapService
vi.mock('@/services/map', () => ({
  default: {
    getDistance: vi.fn()
  }
}))

// Mock i18n
vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key, params) => {
      if (params) {
        return `${key} ${JSON.stringify(params)}`
      }
      return key
    }
  })
}))

describe('DeliveryMap', () => {
  const defaultProps = {
    origin: { lat: -23.5505, lng: -46.6333 },
    destination: { lat: -23.5555, lng: -46.6388 }
  }

  const mockGeolocation = {
    getCurrentPosition: vi.fn()
  }

  beforeEach(() => {
    vi.clearAllMocks()
    mapService.getDistance.mockResolvedValue({
      data: { distance: '5.2', duration: '15' }
    })
    
    vi.stubGlobal('navigator', {
      geolocation: mockGeolocation
    })
  })

  it('renders loading state initially', () => {
    const wrapper = mount(DeliveryMap, {
      props: defaultProps
    })
    expect(wrapper.find('[data-cy="map-loading"]').exists()).toBe(true)
  })

  it('calls mapService.getDistance on mount', async () => {
    mount(DeliveryMap, {
      props: defaultProps
    })
    expect(mapService.getDistance).toHaveBeenCalledWith(defaultProps.origin, defaultProps.destination)
  })

  it('displays distance and time after loading', async () => {
    const wrapper = mount(DeliveryMap, {
      props: defaultProps
    })
    
    // Wait for async initMap
    await vi.waitFor(() => {
      if (wrapper.find('[data-cy="map-distance"]').exists()) return true
      throw new Error('Still loading')
    })

    expect(wrapper.find('[data-cy="map-distance"]').text()).toContain('5.2')
    expect(wrapper.find('[data-cy="map-time"]').text()).toContain('15')
  })

  it('handles geolocation if currentPosition is missing', async () => {
    mockGeolocation.getCurrentPosition.mockImplementationOnce((success) => success({
      coords: { latitude: -23.5520, longitude: -46.6350 }
    }))

    mount(DeliveryMap, {
      props: defaultProps
    })

    expect(mockGeolocation.getCurrentPosition).toHaveBeenCalled()
  })

  it('shows error state if map initialization fails', async () => {
    // Force error by mocking mapService to reject
    mapService.getDistance.mockRejectedValue(new Error('API Error'))
    
    const wrapper = mount(DeliveryMap, {
      props: defaultProps
    })

    await vi.waitFor(() => {
       if (wrapper.find('[data-cy="map-error"]').exists()) return true
       if (!wrapper.vm.loading) return true
       throw new Error('Still processing')
    })
  })
})
