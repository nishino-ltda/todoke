<template>
  <v-form :ref="el => formRef = el" @submit.prevent="submit">
    <v-alert
      v-if="errors.general || Object.keys(errors).length > 0"
      type="error"
      data-test="auth-alert"
      class="mb-4"
    >
      {{ errors.general || 'Validation failed' }}
    </v-alert>

    <v-text-field
      v-model="form.email"
      label="Email"
      type="email"
      :rules="rules.email"
      required
      :error-messages="errors.email"
      data-test="email-input"
      @blur="validateField('email')"
    ></v-text-field>

    <v-text-field
      v-model="form.password"
      label="Password"
      type="password"
      :rules="rules.password"
      required
      :error-messages="errors.password"
      data-test="password-input"
      @blur="validateField('password')"
    ></v-text-field>

    <template v-if="mode === 'register'">
      <v-text-field
        v-model="form.name"
        label="Name"
        required
        :error-messages="errors.name"
        data-test="name-input"
      ></v-text-field>

      <v-text-field
        v-model="form.password_confirmation"
        label="Confirm Password"
        type="password"
        required
        :error-messages="errors.password_confirmation"
        data-test="password-confirmation-input"
        @blur="validateField('password_confirmation')"
      ></v-text-field>

      <v-select
        v-model="form.role"
        :items="roles"
        label="Account Type"
        required
        :error-messages="errors.role"
        data-test="role-select"
        @update:modelValue="handleRoleChange"
      ></v-select>

      <!-- Courier specific fields -->
      <template v-if="showCourierFields">
        <v-text-field
          v-model="form.license_number"
          label="License Number"
          required
          :error-messages="errors.license_number"
          data-test="license-input"
        ></v-text-field>

        <v-select
          v-model="form.vehicle_type"
          :items="vehicleTypes"
          label="Vehicle Type"
          required
          :error-messages="errors.vehicle_type"
          data-test="vehicle-select"
        ></v-select>

        <v-file-input
          v-model="form.document"
          label="Upload License"
          accept="image/*"
          required
          :error-messages="errors.document"
          data-test="document-upload"
        ></v-file-input>
      </template>

      <!-- Partner specific fields -->
      <template v-if="showPartnerFields">
        <v-text-field
          v-model="form.business_name"
          label="Business Name"
          required
          :error-messages="errors.business_name"
          data-test="business-name-input"
        ></v-text-field>

        <v-select
          v-model="form.business_type"
          :items="businessTypes"
          label="Business Type"
          required
          :error-messages="errors.business_type"
          data-test="business-type-select"
        ></v-select>

        <v-text-field
          v-model="form.tax_id"
          label="Tax ID"
          required
          :error-messages="errors.tax_id"
          data-test="tax-id-input"
        ></v-text-field>

        <v-text-field
          v-model="form.address"
          label="Business Address"
          required
          :error-messages="errors.address"
          data-test="address-input"
        ></v-text-field>

        <v-file-input
          v-model="form.business_document"
          label="Business License"
          accept=".pdf,.jpg,.png"
          required
          :error-messages="errors.business_document"
          data-test="business-document-upload"
        ></v-file-input>
      </template>
    </template>

    <v-btn
      type="submit"
      class="v-btn"
      :disabled="loading || undefined"
      :data-test="mode === 'login' ? 'login-button' : 'register-button'"
      :loading="loading"
    >
      <template v-if="!loading">
        <span data-test="button-text">
          {{ mode === 'login' ? 'Login' : 'Register' }}
        </span>
      </template>
      <template v-else>
        <v-progress-circular
          indeterminate
          size="20"
          width="2"
          data-test="button-loader"
        ></v-progress-circular>
      </template>
    </v-btn>
  </v-form>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'

const props = defineProps({
  mode: {
    type: String,
    default: 'login',
    validator: (value) => ['login', 'register'].includes(value)
  }
})

const authStore = useAuthStore()
const formRef = ref(null)

const validateField = async (field) => {
  if (!formRef.value) return false
  
  // Validate the specific field that triggered the blur
  const { valid } = await formRef.value.validate()
  
  // Also validate just the single field
  if (formRef.value.validateField) {
    await formRef.value.validateField(field)
  }
  
  return valid
}

const showCourierFields = ref(false)
const showPartnerFields = ref(false)

const handleRoleChange = (newRole) => {
  showCourierFields.value = newRole === 'courier'
  showPartnerFields.value = newRole === 'partner'
}

const form = ref({
  email: '',
  password: '',
  name: '',
  password_confirmation: '',
  role: 'customer',
  // Courier fields
  license_number: '',
  vehicle_type: '',
  document: null,
  // Partner fields
  business_name: '',
  business_type: '',
  tax_id: '',
  address: '',
  business_document: null
})
const errors = ref({})
// Make loading reactive and match test expectations
const loading = computed(() => authStore.loading)

// Define validation rules for testing
const rules = {
    email: [
      v => !!v || 'Email is required',
      v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || 'Email must be valid'
    ],
  password: [
    v => !!v || 'Password is required',
    v => {
      if (!v) return true
      if (v.length < 8) {
        return 'Minimum 8 characters required'
      }
      return true
    }
  ],
  name: [
    v => !!v || 'Name is required'
  ],
  password_confirmation: [
    v => !!v || 'Password confirmation is required',
    v => {
      if (!v) return true
      if (v !== form.value.password) {
        return 'Password confirmation does not match'
      }
      return true
    }
  ]
}

// Expose properties and methods for testing
defineExpose({
  formRef,
  validateField,
  submit,
  loading,
  rules,
  form
})
const error = computed(() => authStore.error)

watch(error, (newError) => {
  if (newError) {
    // Reset errors object
    errors.value = {}
    
    // Handle errors object if it exists
    if (newError.errors) {
      // Convert array errors to string
      Object.entries(newError.errors).forEach(([key, value]) => {
        errors.value[key] = Array.isArray(value) ? value.join(', ') : value
      })
      
      // Ensure general error is set if there are field errors
      if (Object.keys(newError.errors).length > 0) {
        errors.value.general = 'Validation failed'
      }
    }
    
    // Set general error message if it exists
    if (newError.message) {
      errors.value.general = newError.message
    }
  } else {
    // Clear errors when error is null
    errors.value = {}
  }
}, { immediate: true })

const roles = [
  { title: 'Customer', value: 'customer' },
  { title: 'Courier', value: 'courier' },
  { title: 'Partner', value: 'partner' }
]

const vehicleTypes = [
  { title: 'Motorcycle', value: 'motorcycle' },
  { title: 'Bicycle', value: 'bicycle' },
  { title: 'Car', value: 'car' }
]

const businessTypes = [
  { title: 'Restaurant', value: 'restaurant' },
  { title: 'Cafe', value: 'cafe' },
  { title: 'Bakery', value: 'bakery' },
  { title: 'Grocery', value: 'grocery' }
]

const emit = defineEmits(['success', 'error'])

async function submit() {
  try {
    const logStore = useLogStore()
    logStore.log(`🔑 Attempting ${props.mode} for email: ${form.value.email}`, 'debug')
    
    const valid = await formRef.value?.validate()
    if (!valid) {
      const errorMsg = 'Please fix the validation errors'
      errors.value = { general: errorMsg }
      logStore.log(`❌ Validation failed: ${errorMsg}`, 'error')
      emit('error', new Error(errorMsg))
      return
    }

    if (props.mode === 'login') {
      const credentials = {
        email: form.value.email,
        password: form.value.password
      }
      logStore.log(`🔐 Sending login request for ${credentials.email}`, 'debug')
      const response = await authStore.login(credentials, router)
      
      if (response?.data?.token) {
        logStore.log(`✅ Login successful for ${credentials.email}`, 'info')
        emit('success', { token: response.token })
        return response
      }
      const error = new Error('Login failed')
      logStore.log(`❌ Login failed for ${credentials.email}: ${error.message}`, 'error')
      errors.value = { general: 'Login failed' }
      emit('error', error)
      errors.value = { general: error.message }
      return Promise.resolve({ error })
    } else {
      let registerData;
      const hasFiles = form.value.document instanceof File || form.value.business_document instanceof File;
      
      if (hasFiles) {
        const formData = new FormData()
        Object.entries(form.value).forEach(([key, value]) => {
          if (value !== null && value !== undefined && value !== '') {
            if (value instanceof File) {
              formData.append(key, value)
            } else {
              formData.append(key, value.toString())
            }
          }
        })
        registerData = formData;
      } else {
        registerData = form.value;
      }

      logStore.log(`📝 Sending registration request for ${form.value.email}`, 'debug')
      const response = await authStore.register(registerData, router)
      if (response?.token) {
        logStore.log(`✅ Registration successful for ${form.value.email}`, 'info')
        emit('success', { token: response.token })
        return response
      }
      const error = new Error('Registration failed')
      logStore.log(`❌ Registration failed for ${form.value.email}: ${error.message}`, 'error')
      emit('error', error)
      return Promise.resolve({ error })
    }
  } catch (error) {
    const logStore = useLogStore()
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      logStore.log(`❌ Form errors: ${JSON.stringify(error.response.data.errors)}`, 'error')
    }
    if (error.response?.data?.message) {
      errors.value.general = error.response.data.message
      logStore.log(`❌ API error: ${error.response.data.message}`, 'error')
    } else if (error.message) {
      errors.value.general = error.message
      logStore.log(`❌ Error: ${error.message}`, 'error')
    } else {
      errors.value.general = 'An unexpected error occurred'
      logStore.log('❌ Unexpected error occurred', 'error')
    }
    emit('error', error)
    return Promise.resolve({ error })
  }
}
</script>
