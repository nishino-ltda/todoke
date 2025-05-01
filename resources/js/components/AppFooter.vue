<template>
  <v-footer app color="primary" dark class="app-footer">
    <v-container>
      <v-row justify="space-between" align="center">
        <v-col cols="12" md="4">
          <p>&copy; {{ currentYear }} TODOKE</p>
        </v-col>
        
        <v-col cols="12" md="4" class="text-center">
          <router-link to="/terms" class="mr-4">Terms</router-link>
          <router-link to="/privacy">Privacy</router-link>
        </v-col>

        <v-col cols="12" md="4" class="text-right">
          <a href="https://facebook.com" class="social-link mr-2">Facebook</a>
          <a href="https://twitter.com" class="social-link mr-2">Twitter</a>
          <a href="https://instagram.com" class="social-link">Instagram</a>
        </v-col>
      </v-row>
    </v-container>
  </v-footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.social-link {
  color: white;
  text-decoration: none;
}
</style>
