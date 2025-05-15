<template>
  <GuestLayout>
    <v-row justify="center">
      <v-col cols="12" sm="8" md="6">
        <v-card>
          <v-card-title class="text-center">Login</v-card-title>
          <v-card-text>
            <AuthForm 
              mode="login" 
              @success="handleSuccess"
              data-test="login-form"
            />
          </v-card-text>
          <v-card-actions class="justify-center">
            <Link :href="route('register')" class="text-primary" data-test="register-link">Create an account</Link>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </GuestLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import AuthForm from '@/components/AuthForm.vue'

function handleSuccess() {
  // Use Inertia's router for proper SPA navigation
  router.visit('/partner/dashboard')
}
</script>
