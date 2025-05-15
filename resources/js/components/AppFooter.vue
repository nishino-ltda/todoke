<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'


const currentYear = computed(() => new Date().getFullYear())
</script>

<template>
  <v-footer app color="primary" dark class="app-footer">
    <v-container>
      <v-row justify="space-between" align="center">
        <v-col cols="12" md="4">
          <p>&copy; {{ currentYear }} TODOKE</p>
        </v-col>
        
        <v-col cols="12" md="4" class="text-center">
          <Link :href="route('terms')" class="text-white mr-2" data-test="terms-link">Terms</Link>
          <Link :href="route('privacy')" class="text-white" data-test="privacy-link">Privacy</Link>
        </v-col>

        <v-col cols="12" md="4" class="text-right">
          <v-btn variant="text" color="white" href="https://facebook.com" class="mr-2" data-test="facebook-link">
            <v-icon left>mdi-facebook</v-icon>
            Facebook
          </v-btn>
          <v-btn variant="text" color="white" href="https://twitter.com" class="mr-2" data-test="twitter-link">
            <v-icon left>mdi-twitter</v-icon>
            Twitter
          </v-btn>
          <v-btn variant="text" color="white" href="https://instagram.com" data-test="instagram-link">
            <v-icon left>mdi-instagram</v-icon>
            Instagram
          </v-btn>
        </v-col>
      </v-row>
    </v-container>
  </v-footer>
</template>


<style scoped>
.social-link {
  color: white;
  text-decoration: none;
}
</style>
