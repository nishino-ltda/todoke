import api from './api';
import { useLogStore } from '@/stores/log';

export const mapService = {
  /**
   * Geocode an address to coordinates
   * @param {string} address 
   */
  async geocode(address) {
    const logStore = useLogStore();
    logStore.log(`🗺️ Geocoding address: ${address}`);
    try {
      const response = await api.get('/map/geocode', { params: { address } });
      logStore.log('✅ Geocoding successful');
      return response;
    } catch (error) {
      logStore.log(`❌ Geocoding failed for: ${address}`, 'error');
      throw error;
    }
  },

  /**
   * Reverse geocode coordinates to an address
   * @param {number} lat 
   * @param {number} lng 
   */
  async reverseGeocode(lat, lng) {
    const logStore = useLogStore();
    logStore.log(`🗺️ Reverse geocoding coordinates: ${lat}, ${lng}`);
    try {
      const response = await api.get('/map/reverse-geocode', { params: { lat, lng } });
      logStore.log('✅ Reverse geocoding successful');
      return response;
    } catch (error) {
      logStore.log(`❌ Reverse geocoding failed for: ${lat}, ${lng}`, 'error');
      throw error;
    }
  },

  /**
   * Get distance and time between two points
   * @param {Object} origin {lat, lng}
   * @param {Object} destination {lat, lng}
   */
  async getDistance(origin, destination) {
    const logStore = useLogStore();
    logStore.log('🗺️ Calculating distance between points');
    try {
      const response = await api.get('/map/distance', { 
        params: { 
          origin_lat: origin.lat, 
          origin_lng: origin.lng,
          dest_lat: destination.lat,
          dest_lng: destination.lng
        } 
      });
      logStore.log('✅ Distance calculation successful');
      return response;
    } catch (error) {
      logStore.log('❌ Distance calculation failed', 'error');
      throw error;
    }
  }
};

export default mapService;
