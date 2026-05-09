import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import userService from '../services/user';

export const useUserStore = defineStore('user', () => {
  const profile = ref(null);
  const loading = ref(false);
  const error = ref(null);

  const isLoggedIn = computed(() => !!profile.value);

  async function fetchProfile() {
    loading.value = true;
    error.value = null;
    try {
      const response = await userService.getProfile();
      profile.value = response.data;
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch profile';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function updateProfile(data) {
    loading.value = true;
    error.value = null;
    try {
      const response = await userService.updateProfile(data);
      profile.value = response.data;
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update profile';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  function setProfile(data) {
    profile.value = data;
  }

  function clearProfile() {
    profile.value = null;
  }

  return {
    profile,
    loading,
    error,
    isLoggedIn,
    fetchProfile,
    updateProfile,
    setProfile,
    clearProfile
  };
});
