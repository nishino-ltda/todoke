<template>
  <v-form @submit.prevent="submit">
    <v-text-field
      v-model="form.email"
      label="Email"
      type="email"
      required
      :error-messages="errors.email"
      data-test="email-input"
    ></v-text-field>

    <v-text-field
      v-model="form.password"
      label="Password"
      type="password"
      required
      :error-messages="errors.password"
      data-test="password-input"
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
        data-test="password-confirm-input"
      ></v-text-field>

      <v-select
        v-model="form.role"
        :items="roles"
        label="Account Type"
        required
        :error-messages="errors.role"
        data-test="role-select"
      ></v-select>

      <!-- Courier specific fields -->
      <template v-if="form.role === 'courier'">
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
      <template v-if="form.role === 'partner'">
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
      color="primary"
      :loading="loading"
      data-test="submit-button"
    >
      {{ mode === 'login' ? 'Login' : 'Register' }}
    </v-btn>
  </v-form>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  mode: {
    type: String,
    default: 'login',
    validator: (value) => ['login', 'register'].includes(value)
  }
})

const authStore = useAuthStore()
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
const loading = computed(() => authStore.loading)

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

async function submit() {
  try {
    if (props.mode === 'login') {
      await authStore.login({
        email: form.value.email,
        password: form.value.password
      })
    } else {
      const formData = new FormData()
      formData.append('name', form.value.name)
      formData.append('email', form.value.email)
      formData.append('password', form.value.password)
      formData.append('password_confirmation', form.value.password_confirmation)
      formData.append('role', form.value.role)

      if (form.value.role === 'courier') {
        formData.append('license_number', form.value.license_number)
        formData.append('vehicle_type', form.value.vehicle_type)
        if (form.value.document) {
          formData.append('document', form.value.document)
        }
      } else if (form.value.role === 'partner') {
        formData.append('business_name', form.value.business_name)
        formData.append('business_type', form.value.business_type)
        formData.append('tax_id', form.value.tax_id)
        formData.append('address', form.value.address)
        if (form.value.business_document) {
          formData.append('business_document', form.value.business_document)
        }
      }

      await authStore.register(formData)
    }
  } catch (error) {
    errors.value = error.response?.data?.errors || {}
  }
}
</script>
