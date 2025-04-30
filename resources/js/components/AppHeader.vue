<script setup>
import { useAuthStore } from '@/stores/auth'
import { useLoadingStore } from '@/stores/loading'
import { computed } from 'vue'

const authStore = useAuthStore()
const loadingStore = useLoadingStore()

const isLoading = computed(() => loadingStore.isLoading)
</script>

<template>
  <header class="app-header">
    <div class="logo">TODOKE</div>
    
    <nav class="main-nav">
      <router-link to="/">Home</router-link>
      <router-link to="/menu" v-if="authStore.isAuthenticated">Menu</router-link>
    </nav>

    <div class="auth-section">
      <template v-if="authStore.isAuthenticated">
        <span>Welcome, {{ authStore.user?.name }}</span>
        <button @click="authStore.logout" :disabled="isLoading">
          Logout
        </button>
      </template>
      <template v-else>
        <router-link to="/login">Login</router-link>
        <router-link to="/register">Register</router-link>
      </template>
    </div>
  </header>
</template>

<style scoped>
.app-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background-color: #2c3e50;
  color: white;
}

.logo {
  font-weight: bold;
  font-size: 1.5rem;
}

.main-nav {
  display: flex;
  gap: 1rem;
}

.main-nav a {
  color: white;
  text-decoration: none;
}

.auth-section {
  display: flex;
  gap: 1rem;
  align-items: center;
}

button {
  padding: 0.5rem 1rem;
  background-color: #e74c3c;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
