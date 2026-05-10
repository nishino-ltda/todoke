import deliveryService from '../delivery'
import api from '../api'
import { useLogStore } from '../../stores/log.js'
import { vi } from 'vitest'
import { createTestingPinia } from '@pinia/testing'

describe('Delivery Service', () => {
  let logStore

  beforeEach(() => {
    createTestingPinia()
    logStore = useLogStore()
    vi.clearAllMocks()
  })

  // ─── getAvailableDeliveries ──────────────────────────────────────────────

  describe('getAvailableDeliveries', () => {
    it('should fetch deliveries with status=pending and log the action', async () => {
      const mockResponse = {
        data: {
          deliveries: [
            { id: 1, status: 'pending', fee: 15.00 },
            { id: 2, status: 'pending', fee: 20.00 }
          ]
        }
      }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await deliveryService.getAvailableDeliveries()

      expect(api.get).toHaveBeenCalledWith('/deliveries', {
        params: { status: 'pending' }
      })
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith('📦 Fetching available deliveries')
      expect(logStore.log).toHaveBeenCalledWith('✅ Available deliveries loaded')
    })

    it('should log error and rethrow on failure', async () => {
      const error = new Error('Network Error')
      api.get = vi.fn().mockRejectedValueOnce(error)

      await expect(deliveryService.getAvailableDeliveries()).rejects.toThrow(error)
      expect(logStore.log).toHaveBeenCalledWith(
        '❌ Failed to load available deliveries',
        'error'
      )
    })
  })

  // ─── getMyActiveDelivery ─────────────────────────────────────────────────

  describe('getMyActiveDelivery', () => {
    it('should fetch deliveries with active status params and log the action', async () => {
      const mockResponse = {
        data: {
          deliveries: [{ id: 3, status: 'accepted' }]
        }
      }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await deliveryService.getMyActiveDelivery()

      expect(api.get).toHaveBeenCalledWith('/deliveries', {
        params: { status: 'accepted,arrived,picked_up' }
      })
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith('🚴 Fetching active delivery')
      expect(logStore.log).toHaveBeenCalledWith('✅ Active delivery data loaded')
    })

    it('should log error and rethrow on failure', async () => {
      const error = new Error('Server Error')
      api.get = vi.fn().mockRejectedValueOnce(error)

      await expect(deliveryService.getMyActiveDelivery()).rejects.toThrow(error)
      expect(logStore.log).toHaveBeenCalledWith(
        '❌ Failed to load active delivery',
        'error'
      )
    })
  })

  // ─── acceptDelivery ───────────────────────────────────────────────────────

  describe('acceptDelivery', () => {
    it('should call PATCH /deliveries/{id}/accept and log success', async () => {
      const mockResponse = { data: { id: 5, status: 'accepted' } }
      api.patch = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await deliveryService.acceptDelivery(5)

      expect(api.patch).toHaveBeenCalledWith('/deliveries/5/accept')
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith('✅ Accepting delivery #5')
      expect(logStore.log).toHaveBeenCalledWith('✅ Delivery #5 accepted')
    })

    it('should log error and rethrow on 409 conflict', async () => {
      const error = new Error('Already accepted')
      api.patch = vi.fn().mockRejectedValueOnce(error)

      await expect(deliveryService.acceptDelivery(5)).rejects.toThrow(error)
      expect(logStore.log).toHaveBeenCalledWith(
        '❌ Failed to accept delivery #5',
        'error'
      )
    })
  })

  // ─── rejectDelivery ───────────────────────────────────────────────────────

  describe('rejectDelivery', () => {
    it('should resolve immediately without an API call (local-only)', async () => {
      api.post = vi.fn()
      api.patch = vi.fn()

      const result = await deliveryService.rejectDelivery(7)

      expect(api.post).not.toHaveBeenCalled()
      expect(api.patch).not.toHaveBeenCalled()
      expect(result).toEqual({ data: { id: 7, rejected: true } })
      expect(logStore.log).toHaveBeenCalledWith(
        '❌ Rejecting delivery #7 (local)'
      )
    })
  })

  // ─── updateDeliveryStatus ─────────────────────────────────────────────────

  describe('updateDeliveryStatus', () => {
    it('should call PATCH /deliveries/{id}/status with the status and log success', async () => {
      const mockResponse = { data: { id: 10, status: 'in_transit' } }
      api.patch = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await deliveryService.updateDeliveryStatus(10, 'in_transit')

      expect(api.patch).toHaveBeenCalledWith('/deliveries/10/status', {
        status: 'in_transit'
      })
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith(
        '🔄 Updating delivery #10 status → in_transit'
      )
      expect(logStore.log).toHaveBeenCalledWith(
        '✅ Delivery #10 status updated to in_transit'
      )
    })

    it('should pass extra data (current_position etc.) to the API', async () => {
      const mockResponse = { data: { id: 11, status: 'delivered' } }
      api.patch = vi.fn().mockResolvedValueOnce(mockResponse)

      const extra = { current_position: { lat: -23.5, lng: -46.6 }, confirmation_code: 'ABC123' }
      await deliveryService.updateDeliveryStatus(11, 'delivered', extra)

      expect(api.patch).toHaveBeenCalledWith('/deliveries/11/status', {
        status: 'delivered',
        current_position: { lat: -23.5, lng: -46.6 },
        confirmation_code: 'ABC123'
      })
    })

    it('should log error and rethrow on validation failure', async () => {
      const error = new Error('Invalid status transition')
      api.patch = vi.fn().mockRejectedValueOnce(error)

      await expect(
        deliveryService.updateDeliveryStatus(10, 'invalid')
      ).rejects.toThrow(error)
      expect(logStore.log).toHaveBeenCalledWith(
        '❌ Failed to update delivery #10 status',
        'error'
      )
    })
  })

  // ─── getDeliveryDetails ───────────────────────────────────────────────────

  describe('getDeliveryDetails', () => {
    it('should call GET /deliveries/{id} and log success', async () => {
      const mockResponse = {
        data: {
          id: 15,
          status: 'in_transit',
          origin_address: 'Rua A, 123',
          destination_address: 'Rua B, 456'
        }
      }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await deliveryService.getDeliveryDetails(15)

      expect(api.get).toHaveBeenCalledWith('/deliveries/15')
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith('🔍 Fetching delivery details #15')
      expect(logStore.log).toHaveBeenCalledWith('✅ Delivery #15 details loaded')
    })

    it('should log error and rethrow on 404', async () => {
      const error = new Error('Not Found')
      api.get = vi.fn().mockRejectedValueOnce(error)

      await expect(deliveryService.getDeliveryDetails(999)).rejects.toThrow(error)
      expect(logStore.log).toHaveBeenCalledWith(
        '❌ Failed to load delivery #999 details',
        'error'
      )
    })
  })

  // ─── Legacy aliases ───────────────────────────────────────────────────────

  describe('legacy aliases', () => {
    it('getMyDeliveries should delegate to getAvailableDeliveries', async () => {
      const mockResponse = { data: { deliveries: [] } }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      await deliveryService.getMyDeliveries()

      expect(api.get).toHaveBeenCalledWith('/deliveries', {
        params: { status: 'pending' }
      })
    })

    it('updateStatus should delegate to updateDeliveryStatus', async () => {
      const mockResponse = { data: { id: 1, status: 'collected' } }
      api.patch = vi.fn().mockResolvedValueOnce(mockResponse)

      await deliveryService.updateStatus(1, 'collected')

      expect(api.patch).toHaveBeenCalledWith('/deliveries/1/status', {
        status: 'collected'
      })
    })

    it('getDetails should delegate to getDeliveryDetails', async () => {
      const mockResponse = { data: { id: 1 } }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      await deliveryService.getDetails(1)

      expect(api.get).toHaveBeenCalledWith('/deliveries/1')
    })
  })
})
