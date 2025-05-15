<template>
  <GuestLayout>
    <v-container class="py-4">
      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <h1 class="text-h3 font-weight-bold mb-6" data-test="terms-title">Terms of Service</h1>

          <v-card class="mb-8" variant="text" data-test="terms-section-1">
            <v-card-title class="text-h5 mb-4">1. General Terms</v-card-title>
            <v-card-text>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris.</v-card-text>
          </v-card>

          <v-card class="mb-8" variant="text" data-test="terms-section-2">
            <v-card-title class="text-h5 mb-4">2. User Responsibilities</v-card-title>
            <v-card-text>Vivamus luctus eros aliquet convallis ultricies. Mauris augue massa, ultricies non ligula.</v-card-text>
          </v-card>

          <v-card class="mb-8" variant="text" data-test="terms-section-3">
            <v-card-title class="text-h5 mb-4">3. Limitation of Liability</v-card-title>
            <v-card-text>Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare.</v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
</script>
