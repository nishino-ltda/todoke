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
  }
};

export default adminService;
