<template>
  <v-form @submit.prevent="submit">
    <v-alert
      v-if="error"
      type="error"
      data-cy="error-alert"
      class="mb-4"
    >
      {{ error }}
    </v-alert>

    <v-alert
      v-if="success"
      type="success"
      data-cy="success-alert"
      class="mb-4"
    >
      Password reset link has been sent to your email
    </v-alert>

    <v-text-field
      v-model="email"
      label="Email"
      type="email"
      required
      :error-messages="errors.email"
      data-cy="email-input"
    ></v-text-field>

    <v-btn
      type="submit"
      color="primary"
      :loading="loading"
      data-cy="submit-button"
    >
      Send Reset Link
    </v-btn>
  </v-form>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'

const authStore = useAuthStore()
const logStore = useLogStore()

const email = ref('')
const errors = ref({})
const error = ref('')
const success = ref(false)
const loading = ref(false)

async function submit() {
  loading.value = true
  error.value = ''
  errors.value = {}
  success.value = false

  try {
    logStore.log('📧 Attempting password reset request')
    await authStore.requestPasswordReset(email.value)
    success.value = true
    logStore.log('✅ Password reset request successful')
  } catch (err) {
    error.value = err.message || 'Failed to send reset link'
    errors.value = err.errors || {}
    logStore.log(`❌ Password reset failed: ${error.value}`)
  } finally {
    loading.value = false
  }
}
</script>
