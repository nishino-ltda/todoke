<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
</script>

<template>
  <AppLayout title="Partner Dashboard">
    <template #header>
      <v-toolbar-title class="text-h5">
        Partner Dashboard
      </v-toolbar-title>
    </template>

    <v-container class="py-12">
      <v-row justify="center">
        <v-col cols="12" md="8" lg="6">
          <v-card>
            <v-card-text>
              Welcome to your partner dashboard!
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>
