<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
</script>

<template>
  <AppLayout title="Courier Dashboard">
    <template #header>
      <v-toolbar-title class="text-h5">
        Courier Dashboard
      </v-toolbar-title>
    </template>

    <v-container class="py-12">
      <v-row justify="center">
        <v-col cols="12" md="8" lg="6">
          <v-card>
            <v-card-text>
              Welcome to your courier dashboard!
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>
