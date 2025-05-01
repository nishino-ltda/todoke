<template>
  <v-app-bar app color="primary" dark>
    <v-app-bar-nav-icon @click="toggleDrawer" />
    <v-toolbar-title>TODOKE</v-toolbar-title>
    <v-spacer />

    <template v-if="isAuthenticated">
      <span class="welcome-message">Welcome, {{ user?.name }}</span>
      <v-btn text to="/menu">Menu</v-btn>
      <v-btn text disabled v-if="loading">Loading...</v-btn>
      <v-btn text @click="handleLogout" :disabled="loading">
        Logout
      </v-btn>
    </template>

    <template v-else>
      <v-btn text to="/login">Login</v-btn>
      <v-btn text to="/register">Register</v-btn>
    </template>
  </v-app-bar>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const router = useRouter()
const authStore = useAuthStore()

const { isAuthenticated, user, loading } = storeToRefs(authStore)
const handleLogout = authStore.logout

function toggleDrawer() {
  // Will implement drawer toggle later
}
</script>
