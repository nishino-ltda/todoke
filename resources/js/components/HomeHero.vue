<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <v-container fluid class="pa-0">
    <v-row no-gutters class="fill-height" align="center">
      <v-col cols="12" md="6" class="pa-8">
        <div class="text-center text-md-left">
          <h1 class="text-h3 text-md-h2 font-weight-bold mb-4">
            Hybrid Delivery with Community Pricing
          </h1>
          <p class="text-h6 mb-6">
            Combining motorbike couriers and drones for fast, fair deliveries
          </p>
          <div class="d-flex flex-column flex-sm-row justify-center justify-md-start gap-4">
            <Link
              :href="route('register')"
              class="v-btn bg-primary text-white v-btn--size-x-large"
              data-test="hero-register-link"
            >
              Get Started
            </Link>
            <Link
              :href="route('login')"
              class="v-btn v-btn--variant-outlined v-btn--size-x-large"
              data-test="hero-login-link"
            >
              Sign In
            </Link>
          </div>
        </div>
      </v-col>
      <v-col cols="12" md="6" class="hero-image">
        <v-img
          src="/images/hero-image.jpg"
          alt="Hybrid delivery illustration"
          cover
          height="100%"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
</script>

<style scoped>
.hero-container {
  height: 80vh;
  min-height: 600px;
  background: linear-gradient(to right, #ffffff 50%, #f5f5f5 50%);
}

.hero-content {
  padding: 2rem;
}

.hero-image {
  height: 100%;
}

@media (max-width: 960px) {
  .hero-container {
    height: auto;
    background: #ffffff;
  }
  
  .hero-image {
    height: 400px;
  }
}
</style>
