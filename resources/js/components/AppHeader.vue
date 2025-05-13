<template>
  <v-app-bar app color="primary" dark>
    <v-app-bar-nav-icon @click="toggleDrawer" />
    <v-toolbar-title>TODOKE</v-toolbar-title>
    <v-spacer />

    <template v-if="isAuthenticated">
      <span class="welcome-message">Welcome, {{ user?.name }}</span>
      <Link :href="route('menu')" class="text-white mr-2" data-test="menu-link">Menu</Link>
      <v-btn text disabled v-if="loading">Loading...</v-btn>
      <CartIcon />
      <v-btn text @click="handleLogout" :disabled="loading">
        Logout
      </v-btn>
    </template>

    <template v-else>
      <Link :href="route('login')" class="text-white mr-2" data-test="login-link">Login</Link>
      <Link :href="route('register')" class="text-white" data-test="register-link">Register</Link>
    </template>
  </v-app-bar>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { Link } from '@inertiajs/vue3'
import CartIcon from './CartIcon.vue'
const authStore = useAuthStore()

const { isAuthenticated, user, loading } = storeToRefs(authStore)
const handleLogout = authStore.logout

function toggleDrawer() {
  // Will implement drawer toggle later
}
</script>
