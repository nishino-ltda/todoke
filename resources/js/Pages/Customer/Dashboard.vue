<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
</script>

<template>
  <AppLayout title="Customer Dashboard">
    <template #header>
      <v-toolbar-title class="text-h5" data-test="dashboard-title">
        Customer Dashboard
      </v-toolbar-title>
    </template>

    <v-container class="py-12">
      <v-row justify="center">
        <v-col cols="12" md="8" lg="6">
          <v-card data-test="welcome-card">
            <v-card-text data-test="welcome-message">
              Welcome to your customer dashboard!
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>
