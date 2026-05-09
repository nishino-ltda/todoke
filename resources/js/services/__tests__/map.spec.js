import mapService from '../map'
import api from '../api'
import { useLogStore } from '../../stores/log.js'
import { vi } from 'vitest'
import { createTestingPinia } from '@pinia/testing'

describe('Map Service', () => {
  let logStore

  beforeEach(() => {
    createTestingPinia()
    logStore = useLogStore()
    vi.clearAllMocks()
  })

  describe('geocode', () => {
    it('should call geocode endpoint with address and log action', async () => {
      const address = '123 Test St'
      const mockResponse = { data: { lat: 10, lng: 20 } }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await mapService.geocode(address)

      expect(api.get).toHaveBeenCalledWith('/map/geocode', { params: { address } })
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith(`🗺️ Geocoding address: ${address}`)
      expect(logStore.log).toHaveBeenCalledWith('✅ Geocoding successful')
    })

    it('should log error on failure', async () => {
      const address = 'Invalid Address'
      const error = new Error('Not Found')
      api.get = vi.fn().mockRejectedValueOnce(error)

      await expect(mapService.geocode(address)).rejects.toThrow(error)
      expect(logStore.log).toHaveBeenCalledWith(`❌ Geocoding failed for: ${address}`, 'error')
    })
  })

  describe('reverseGeocode', () => {
    it('should call reverse-geocode endpoint with coords and log action', async () => {
      const lat = 10
      const lng = 20
      const mockResponse = { data: { address: '123 Test St' } }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await mapService.reverseGeocode(lat, lng)

      expect(api.get).toHaveBeenCalledWith('/map/reverse-geocode', { params: { lat, lng } })
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith(`🗺️ Reverse geocoding coordinates: ${lat}, ${lng}`)
      expect(logStore.log).toHaveBeenCalledWith('✅ Reverse geocoding successful')
    })
  })

  describe('getDistance', () => {
    it('should call distance endpoint with origin and destination and log action', async () => {
      const origin = { lat: 10, lng: 20 }
      const destination = { lat: 11, lng: 21 }
      const mockResponse = { data: { distance: 1.5, time: 300 } }
      api.get = vi.fn().mockResolvedValueOnce(mockResponse)

      const result = await mapService.getDistance(origin, destination)

      expect(api.get).toHaveBeenCalledWith('/map/distance', { 
        params: { 
          origin_lat: origin.lat, 
          origin_lng: origin.lng,
          dest_lat: destination.lat,
          dest_lng: destination.lng
        } 
      })
      expect(result).toEqual(mockResponse)
      expect(logStore.log).toHaveBeenCalledWith('🗺️ Calculating distance between points')
      expect(logStore.log).toHaveBeenCalledWith('✅ Distance calculation successful')
    })
  })
})
