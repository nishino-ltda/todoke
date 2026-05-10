import api from './api';

export const adminService = {
  /**
   * Get system-wide stats
   */
  async getSystemStats() {
    return api.get('/admin/stats');
  },

  /**
   * Get all users (with filtering)
   * @param {Object} params 
   */
  async getUsers(params = {}) {
    return api.get('/admin/users', { params });
  },

  /**
   * Manage user account status
   * @param {string|number} userId 
   * @param {string} action - 'activate' | 'deactivate'
   */
  async manageUser(userId, action) {
    return api.post(`/admin/users/${userId}/${action}`);
  },

  /**
   * Get all nodes
   */
  async getNodes() {
    return api.get('/admin/nodes');
  },

  /**
   * Approve/Reject node
   * @param {string|number} nodeId 
   * @param {string} status - 'approved' | 'rejected'
   */
  async updateNodeStatus(nodeId, status) {
    return api.patch(`/admin/nodes/${nodeId}/status`, { status });
  },

  /**
   * Get all regions
   */
  async getRegions() {
    return api.get('/admin/regions');
  },

  /**
   * Create a new region
   * @param {Object} data 
   */
  async createRegion(data) {
    return api.post('/admin/regions', data);
  },

  /**
   * Update a region
   * @param {string|number} regionId 
   * @param {Object} data 
   */
  async updateRegion(regionId, data) {
    return api.put(`/admin/regions/${regionId}`, data);
  },

  /**
   * Delete a region
   * @param {string|number} regionId 
   */
  async deleteRegion(regionId) {
    return api.delete(`/admin/regions/${regionId}`);
  },

  /**
   * Get deliveries monitoring data
   */
  async getDeliveriesMonitoring() {
    return api.get('/admin/deliveries/monitor');
  },

  /**
   * Get all deliveries
   */
  async getDeliveries(params = {}) {
    return api.get('/admin/deliveries', { params });
  },

  /**
   * Get a single delivery
   * @param {string|number} deliveryId
   */
  async getDelivery(deliveryId) {
    return api.get(`/admin/deliveries/${deliveryId}`);
  },

  /**
   * Get admin settings
   */
  async getSettings() {
    return api.get('/admin/settings');
  },

  /**
   * Update admin settings
   * @param {Object} data 
   */
  async updateSettings(data) {
    return api.patch('/admin/settings', data);
  }
};

export default adminService;
