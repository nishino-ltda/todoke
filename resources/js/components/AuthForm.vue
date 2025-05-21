<template>
  <v-form ref="form" @submit.prevent="submit">
    <v-alert
      v-if="errors.general"
      type="error"
      data-test="auth-alert"
      class="mb-4"
    >
      {{ errors.general }}
    </v-alert>

    <v-text-field
      v-model="form.email"
      label="Email"
      type="email"
      :rules="[
        v => !!v || 'Email is required',
        v => /.+@.+\..+/.test(v) || 'Email must be valid'
      ]"
      required
      :error-messages="errors.email"
      data-test="email-input"
      @blur="validateField('email')"
    ></v-text-field>

    <v-text-field
      v-model="form.password"
      label="Password"
      type="password"
      :rules="[v => !!v || 'Password is required']"
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

    <button
      type="submit"
      class="v-btn"
      :disabled="loading"
      :data-test="mode === 'login' ? 'login-button' : 'register-button'"
    >
      <template v-if="!loading">
        <span data-test="button-text">
          {{ mode === 'login' ? 'Login' : 'Register' }}
        </span>
      </template>
      <template v-if="loading">
        <div class="v-progress-circular" data-test="button-loader"></div>
      </template>
    </button>
  </v-form>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  mode: {
    type: String,
    default: 'login',
    validator: (value) => ['login', 'register'].includes(value)
  }
})

const router = useRouter()
const authStore = useAuthStore()
const formRef = ref(null)

const validateField = async (field) => {
  if (formRef.value) {
    await formRef.value.validate()
  }
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
// Make loading reactive
const loading = computed(() => authStore.loading)

// Expose properties and methods for testing
defineExpose({
  formRef,
  validateField,
  submit,
  loading
})
const error = computed(() => authStore.error)

watch(error, (newError) => {
  if (newError) {
    // Initialize errors object with empty object
    errors.value = { ...errors.value }
    
    // Handle errors object if it exists
    if (newError.errors) {
      Object.assign(errors.value, newError.errors)
    }
    
    // Set general error message if it exists
    if (newError.message) {
      errors.value.general = newError.message
    }
  } else {
    // Clear errors when error is null
    errors.value = {}
  }
})

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
      const valid = await formRef.value?.validate()
      if (!valid) {
        errors.value = {
          general: 'Please fix the validation errors'
        }
        return
      }
    
    if (props.mode === 'login') {
      const credentials = {
        email: form.value.email,
        password: form.value.password
      }
      const response = await authStore.login(credentials, router)
      if (response?.token) {
        emit('success', { token: response.token })
      }
    } else {
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

      const response = await authStore.register(formData, router)
      if (response?.token) {
        emit('success', { token: response.token })
      }
    }
    } catch (error) {
      errors.value = error.response?.data?.errors || {}
      if (error.response?.data?.message) {
        errors.value.general = error.response.data.message
      } else if (!Object.keys(errors.value).length) {
        errors.value.general = 'An unexpected error occurred'
      }
      if (error) {
        emit('error', error)
      }
  }
}
</script>
