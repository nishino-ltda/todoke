<template>
  <v-form @submit.prevent="handleSubmit" class="auth-form">
    <!-- Name field (only for registration) -->
    <v-text-field
      v-if="mode === 'register'"
      v-model="form.name"
      label="Name"
      required
      :error-messages="errors?.name"
      data-test="name-input"
    ></v-text-field>
    
    <!-- Email field -->
    <v-text-field
      v-model="form.email"
      label="Email"
      type="email"
      required
      :error-messages="errors?.email"
      data-test="email-input"
    ></v-text-field>
    
    <!-- Password field -->
    <v-text-field
      v-model="form.password"
      label="Password"
      type="password"
      required
      :error-messages="errors?.password"
      data-test="password-input"
    ></v-text-field>

    <!-- Password confirmation (only for registration) -->
    <v-text-field
      v-if="mode === 'register'"
      v-model="form.password_confirmation"
      label="Confirm Password"
      type="password"
      required
      :error-messages="errors?.password_confirmation"
      data-test="password-confirmation-input"
    ></v-text-field>

    <!-- Submit button -->
    <v-btn 
      type="submit" 
      color="primary"
      :loading="loading"
      :loading-text="loadingText"
      block
      data-test="submit-button"
    >
      {{ submitButtonText }}
    </v-btn>

    <!-- Error alert -->
    <v-alert
      v-if="generalError"
      type="error"
      class="mt-4"
      data-test="error-alert"
    >
      {{ generalError }}
    </v-alert>
  </v-form>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'

const props = defineProps({
  mode: {
    type: String,
    required: true,
    validator: (value) => ['login', 'register'].includes(value)
  }
})

const emit = defineEmits(['success', 'error'])

const authStore = useAuthStore()
const { loading, error } = storeToRefs(authStore)

// Form data
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

// General error message
const generalError = ref('')

// Computed properties for UI elements
const submitButtonText = computed(() => props.mode === 'login' ? 'Login' : 'Register')
const loadingText = computed(() => props.mode === 'login' ? 'Logging in...' : 'Registering...')

// Form validation errors
const errors = ref({})

// Client-side validation
const validateForm = () => {
  errors.value = {}
  
  // Email validation
  if (form.value.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = ['The email must be a valid email address.']
  }
  
  // Password confirmation validation
  if (props.mode === 'register' && 
      form.value.password && 
      form.value.password_confirmation && 
      form.value.password !== form.value.password_confirmation) {
    errors.value.password = ['The password confirmation does not match.']
  }
  
  // Always return true to allow API calls
  return true
}

// Watch for error changes to update general error
watch(error, (newError) => {
  if (newError) {
    if (typeof newError === 'string') {
      generalError.value = newError
    } else if (newError.message && !newError.errors) {
      generalError.value = newError.message
    } else {
      generalError.value = ''
    }
  } else {
    generalError.value = ''
  }
})

// Form submission handler
async function handleSubmit() {
  // Run client-side validation
  validateForm()
  
  // Convert errors to match Vuetify format
  Object.keys(errors.value).forEach(key => {
    errors.value[key] = errors.value[key]?.join(', ')
  })
  
  // Don't return early - allow API calls even with validation errors

  try {
    if (props.mode === 'login') {
      await authStore.login(form.value)
    } else {
      await authStore.register({
        ...form.value,
        // Ensure we use the expected API endpoint
        _endpoint: '/api/auth/register'
      })
    }
    
    // Reset form on success
    resetForm()
    emit('success')
  } catch (err) {
    console.error(`${props.mode} failed:`, err)
    emit('error', err)
    
    // Handle backend validation errors
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      generalError.value = err.response?.data?.message || err.message || 'An error occurred'
    }
    
    // Reset password fields on error
    form.value.password = ''
    if (props.mode === 'register') {
      form.value.password_confirmation = ''
    }
  }
}

// Reset form
function resetForm() {
  form.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  }
}
</script>

<style scoped>
.auth-form {
  width: 100%;
}
</style>
