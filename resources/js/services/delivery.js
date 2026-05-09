import api from './api';

export const deliveryService = {
  /**
   * Get all deliveries for the current user (if courier)
   */
  async getMyDeliveries() {
    return api.get('/courier/deliveries');
  },

  /**
   * Update delivery status
   * @param {string|number} deliveryId 
   * @param {string} status 
   */
  async updateStatus(deliveryId, status) {
    return api.patch(`/deliveries/${deliveryId}/status`, { status });
  },

  /**
   * Get delivery details
   * @param {string|number} deliveryId 
   */
  async getDetails(deliveryId) {
    return api.get(`/deliveries/${deliveryId}`);
  }
};

export default deliveryService;
