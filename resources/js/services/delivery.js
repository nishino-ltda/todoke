import api from './api';
import { useLogStore } from '@/stores/log';

export const deliveryService = {
  /**
   * Fetch available deliveries for the courier (pending status)
   * GET /api/v1/deliveries?status=pending
   */
  async getAvailableDeliveries() {
    const logStore = useLogStore();
    logStore.log('📦 Fetching available deliveries');
    try {
      const response = await api.get('/deliveries', { params: { status: 'pending' } });
      logStore.log('✅ Available deliveries loaded');
      return response;
    } catch (error) {
      logStore.log('❌ Failed to load available deliveries', 'error');
      throw error;
    }
  },

  /**
   * Fetch the courier's currently active delivery (if any)
   * GET /api/v1/deliveries?status=accepted,arrived,picked_up
   */
  async getMyActiveDelivery() {
    const logStore = useLogStore();
    logStore.log('🚴 Fetching active delivery');
    try {
      const response = await api.get('/deliveries', {
        params: { status: 'accepted,arrived,picked_up' }
      });
      logStore.log('✅ Active delivery data loaded');
      return response;
    } catch (error) {
      logStore.log('❌ Failed to load active delivery', 'error');
      throw error;
    }
  },

  /**
   * Accept a delivery assignment
   * PATCH /api/v1/deliveries/{id}/accept
   * @param {string|number} deliveryId
   */
  async acceptDelivery(deliveryId) {
    const logStore = useLogStore();
    logStore.log(`✅ Accepting delivery #${deliveryId}`);
    try {
      const response = await api.patch(`/deliveries/${deliveryId}/accept`);
      logStore.log(`✅ Delivery #${deliveryId} accepted`);
      return response;
    } catch (error) {
      logStore.log(`❌ Failed to accept delivery #${deliveryId}`, 'error');
      throw error;
    }
  },

  /**
   * Reject a delivery (local-only — no backend reject endpoint)
   * Returns a resolved promise so callers can treat it uniformly.
   * @param {string|number} deliveryId
   */
  async rejectDelivery(deliveryId) {
    const logStore = useLogStore();
    logStore.log(`❌ Rejecting delivery #${deliveryId} (local)`);
    return Promise.resolve({ data: { id: deliveryId, rejected: true } });
  },

  /**
   * Update delivery status during the delivery process
   * PATCH /api/v1/deliveries/{id}/status
   * @param {string|number} deliveryId
   * @param {string} status - 'collected' | 'in_transit' | 'delivered' | 'canceled'
   * @param {Object} [extra] - Optional: { current_position, confirmation_code, stage_type }
   */
  async updateDeliveryStatus(deliveryId, status, extra = {}) {
    const logStore = useLogStore();
    logStore.log(`🔄 Updating delivery #${deliveryId} status → ${status}`);
    try {
      const response = await api.patch(`/deliveries/${deliveryId}/status`, {
        status,
        ...extra
      });
      logStore.log(`✅ Delivery #${deliveryId} status updated to ${status}`);
      return response;
    } catch (error) {
      logStore.log(`❌ Failed to update delivery #${deliveryId} status`, 'error');
      throw error;
    }
  },

  /**
   * Get full details of a specific delivery
   * GET /api/v1/deliveries/{id}
   * @param {string|number} deliveryId
   */
  async getDeliveryDetails(deliveryId) {
    const logStore = useLogStore();
    logStore.log(`🔍 Fetching delivery details #${deliveryId}`);
    try {
      const response = await api.get(`/deliveries/${deliveryId}`);
      logStore.log(`✅ Delivery #${deliveryId} details loaded`);
      return response;
    } catch (error) {
      logStore.log(`❌ Failed to load delivery #${deliveryId} details`, 'error');
      throw error;
    }
  },

  // ─── Legacy aliases (kept for backwards compatibility) ────────────────────

  /** @deprecated Use getAvailableDeliveries() or getMyActiveDelivery() */
  async getMyDeliveries() {
    return this.getAvailableDeliveries();
  },

  /** @deprecated Use updateDeliveryStatus() */
  async updateStatus(deliveryId, status) {
    return this.updateDeliveryStatus(deliveryId, status);
  },

  /** @deprecated Use getDeliveryDetails() */
  async getDetails(deliveryId) {
    return this.getDeliveryDetails(deliveryId);
  },
};

export default deliveryService;
