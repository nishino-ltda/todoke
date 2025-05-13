<template>
  <GuestLayout>
    <v-container class="py-4">
      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <h1 class="text-h3 font-weight-bold mb-6" data-test="privacy-title">Privacy Policy</h1>

          <v-card class="mb-8" variant="text" data-test="privacy-section-1">
            <v-card-title class="text-h5 mb-4">1. Information Collection</v-card-title>
            <v-card-text>We collect information to provide better services to all our users.</v-card-text>
          </v-card>

          <v-card class="mb-8" variant="text" data-test="privacy-section-2">
            <v-card-title class="text-h5 mb-4">2. Data Usage</v-card-title>
            <v-card-text>The information we collect is used to operate and improve our services.</v-card-text>
          </v-card>

          <v-card class="mb-8" variant="text" data-test="privacy-section-3">
            <v-card-title class="text-h5 mb-4">3. Information Protection</v-card-title>
            <v-card-text>We implement security measures to protect against unauthorized access.</v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue'
</script>
