<template>
  <v-container class="my-12">
    <v-row>
      <v-col cols="12">
        <h2 class="text-h4 text-center mb-8">Why Choose TODOKE?</h2>
      </v-col>
    </v-row>
    
    <v-row>
      <v-col cols="12" md="4">
        <v-card height="100%">
          <v-card-item>
            <v-icon size="x-large" color="primary" class="mb-4">mdi-motorbike</v-icon>
            <v-card-title>Hybrid Delivery</v-card-title>
            <v-card-subtitle>Fast and flexible</v-card-subtitle>
          </v-card-item>
          <v-card-text>
            <p>Combining motorbike couriers and drones for optimal delivery routes and speed.</p>
            <p>Specialized tracking for each delivery stage.</p>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card height="100%">
          <v-card-item>
            <v-icon size="x-large" color="primary" class="mb-4">mdi-account-group</v-icon>
            <v-card-title>Community Pricing</v-card-title>
            <v-card-subtitle>Fair and transparent</v-card-subtitle>
          </v-card-item>
          <v-card-text>
            <p>Couriers democratically determine delivery prices through monthly voting.</p>
            <p>Transparent cost-based pricing dashboard.</p>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card height="100%">
          <v-card-item>
            <v-icon size="x-large" color="primary" class="mb-4">mdi-shield-check</v-icon>
            <v-card-title>Reliable Service</v-card-title>
            <v-card-subtitle>Consistent and secure</v-card-subtitle>
          </v-card-item>
          <v-card-text>
            <p>Comprehensive reputation system ensures quality service.</p>
            <p>Offline functionality for low-connectivity areas.</p>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
</script>

<style scoped>
.v-card {
  transition: transform 0.3s ease;
}

.v-card:hover {
  transform: translateY(-5px);
}
</style>
