import api from './api';

export const userService = {
  /**
   * Get current user profile
   */
  async getProfile() {
    return api.get('/user/profile');
  },

  /**
   * Update user profile
   * @param {Object} data 
   */
  async updateProfile(data) {
    return api.put('/user/profile', data);
  },

  /**
   * Update user preferences
   * @param {Object} preferences 
   */
  async updatePreferences(preferences) {
    return api.patch('/user/preferences', preferences);
  }
};

export default userService;
