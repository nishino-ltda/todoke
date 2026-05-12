import api from './api';

export const partnerService = {
  /**
   * Get partner dashboard stats
   */
  async getDashboardStats() {
    return api.get('/partner/dashboard');
  },

  /**
   * Get partner orders
   */
  async getOrders() {
    return api.get('/partner/orders');
  },

  /**
   * Get single order details
   */
  async getOrder(orderId) {
    return api.get(`/partner/orders/${orderId}`);
  },

  /**
   * Get partner menu items
   */
  async getMenu() {
    return api.get('/partner/menu');
  },

  /**
   * Update menu item availability
   * @param {string|number} itemId 
   * @param {boolean} available 
   */
  async updateItemAvailability(itemId, available) {
    return api.patch(`/partner/menu/${itemId}/availability`, { available });
  },

  /**
   * Update order status
   * @param {string|number} orderId 
   * @param {string} status 
   */
  async updateOrderStatus(orderId, status) {
    return api.patch(`/partner/orders/${orderId}/status`, { status });
  },

  /**
   * Get partner products
   */
  async getProducts() {
    return api.get('/partner/products');
  },

  /**
   * Create a new product
   */
  async createProduct(data) {
    return api.post('/partner/products', data);
  },

  /**
   * Update an existing product
   * @param {string|number} productId 
   * @param {Object} data 
   */
  async updateProduct(productId, data) {
    return api.put(`/partner/products/${productId}`, data);
  },

  /**
   * Delete a product
   * @param {string|number} productId 
   */
  async deleteProduct(productId) {
    return api.delete(`/partner/products/${productId}`);
  },

  /**
   * Get partner addons
   */
  async getAddons() {
    return api.get('/partner/addons');
  },

  /**
   * Create a new addon
   * @param {Object} data 
   */
  async createAddon(data) {
    return api.post('/partner/addons', data);
  },

  /**
   * Update an existing addon
   * @param {string|number} addonId 
   * @param {Object} data 
   */
  async updateAddon(addonId, data) {
    return api.put(`/partner/addons/${addonId}`, data);
  },

  /**
   * Delete an addon
   * @param {string|number} addonId 
   */
  async deleteAddon(addonId) {
    return api.delete(`/partner/addons/${addonId}`);
  },

  /**
   * Get partner regions
   */
  async getRegions() {
    return api.get('/partner/regions');
  },

  /**
   * Create/Update/Delete Region
   */
  async createRegion(data) { return api.post('/partner/regions', data); },
  async updateRegion(id, data) { return api.put(`/partner/regions/${id}`, data); },
  async deleteRegion(id) { return api.delete(`/partner/regions/${id}`); },

  /**
   * Request a courier for an order
   */
  async requestCourier(orderId) {
    return api.post('/deliveries', { order_id: orderId });
  },
  
  /**
   * Settings
   */
  async getSettings() { return api.get('/partner/settings'); },
  async updateSettings(data) { return api.put('/partner/settings', data); }
};

export default partnerService;
