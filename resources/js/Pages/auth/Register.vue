<template>
  <GuestLayout>
    <v-row justify="center">
      <v-col cols="12" sm="8" md="6">
        <v-card>
          <v-card-title class="text-center">Register</v-card-title>
          <v-card-text>
            <AuthForm 
              mode="register" 
              @success="handleSuccess"
              data-test="register-form"
            />
          </v-card-text>
          <v-card-actions class="justify-center">
            <router-link to="/login">Already have an account?</router-link>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </GuestLayout>
</template>

<script setup>
import { useRouter } from 'vue-router'
import GuestLayout from '@/layouts/GuestLayout.vue'
import AuthForm from '@/components/AuthForm.vue'

const router = useRouter()

function handleSuccess() {
  router.push('/')
}
</script>
