<template>
  <GuestLayout>
    <HomeHero />
    <HomeFeatures />
    <div class="text-center mt-8 mb-16">
      <v-btn 
        :to="{ name: 'restaurant', params: { slug: 'tia-mary-corumba' } }"
        color="primary"
        size="large"
      >
        View Tia Mary's Menu
      </v-btn>
    </div>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue'
import HomeHero from '@/components/HomeHero.vue'
import HomeFeatures from '@/components/HomeFeatures.vue'
</script>
