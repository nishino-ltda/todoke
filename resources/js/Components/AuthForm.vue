<template>
  <v-form :ref="el => formRef = el" @submit.prevent="submit">
    <v-alert
      v-if="errors.general || Object.keys(errors).length > 0"
      type="error"
      data-test="auth-alert"
      class="mb-4"
    >
      {{ errors.general || t('auth.validation.general_error') }}
    </v-alert>

    <v-text-field
      v-model="form.email"
      :label="t('auth.form.email')"
      type="email"
      :rules="rules.email"
      required
      :error-messages="errors.email"
      data-test="email-input"
      @blur="validateField('email')"
    ></v-text-field>

    <v-text-field
      v-model="form.password"
      :label="t('auth.form.password')"
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
        :label="t('auth.form.name')"
        required
        :error-messages="errors.name"
        data-test="name-input"
      ></v-text-field>

      <v-text-field
        v-model="form.password_confirmation"
        :label="t('auth.form.confirm_password')"
        type="password"
        required
        :error-messages="errors.password_confirmation"
        data-test="password-confirmation-input"
        @blur="validateField('password_confirmation')"
      ></v-text-field>

      <v-select
        v-model="form.role"
        :items="roles"
        :label="t('auth.form.account_type')"
        required
        :error-messages="errors.role"
        data-test="role-select"
        @update:modelValue="handleRoleChange"
      ></v-select>

      <!-- Courier specific fields -->
      <template v-if="showCourierFields">
        <v-text-field
          v-model="form.license_number"
          :label="t('auth.form.license_number')"
          required
          :error-messages="errors.license_number"
          data-test="license-input"
        ></v-text-field>

        <v-select
          v-model="form.vehicle_type"
          :items="vehicleTypes"
          :label="t('auth.form.vehicle_type')"
          required
          :error-messages="errors.vehicle_type"
          data-test="vehicle-select"
        ></v-select>

        <v-file-input
          v-model="form.document"
          :label="t('auth.form.upload_license')"
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
          :label="t('auth.form.business_name')"
          required
          :error-messages="errors.business_name"
          data-test="business-name-input"
        ></v-text-field>

        <v-select
          v-model="form.business_type"
          :items="businessTypes"
          :label="t('auth.form.business_type')"
          required
          :error-messages="errors.business_type"
          data-test="business-type-select"
        ></v-select>

        <v-text-field
          v-model="form.tax_id"
          :label="t('auth.form.tax_id')"
          required
          :error-messages="errors.tax_id"
          data-test="tax-id-input"
        ></v-text-field>

        <v-text-field
          v-model="form.address"
          :label="t('auth.form.address')"
          required
          :error-messages="errors.address"
          data-test="address-input"
        ></v-text-field>

        <v-file-input
          v-model="form.business_document"
          :label="t('auth.form.upload_business_doc')"
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
          {{ mode === 'login' ? t('auth.login') : t('auth.register') }}
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
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'

const { t } = useI18n()
const logStoreInstance = useLogStore();
logStoreInstance.log('😎 AuthForm component initialized.');

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
      v => !!v || t('auth.validation.required', { field: t('auth.form.email') }),
      v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || t('auth.validation.email')
    ],
  password: [
    v => !!v || t('auth.validation.required', { field: t('auth.form.password') }),
    v => {
      if (!v) return true
      if (v.length < 8) {
        return t('auth.validation.min_length', { length: 8 })
      }
      return true
    }
  ],
  name: [
    v => !!v || t('auth.validation.required', { field: t('auth.form.name') })
  ],
  password_confirmation: [
    v => !!v || t('auth.validation.required', { field: t('auth.form.confirm_password') }),
    v => {
      if (!v) return true
      if (v !== form.value.password) {
        return t('auth.validation.password_match')
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
  errors.value = {}
  
  if (newError) {
    // Handle API error response format
    if (typeof newError === 'string') {
      errors.value.general = newError
    } 
    // Handle error object with message
    else if (newError.message) {
      errors.value.general = newError.message
    }
    // Handle error object with errors array
    else if (newError.errors) {
      Object.entries(newError.errors).forEach(([key, value]) => {
        errors.value[key] = Array.isArray(value) ? value.join(', ') : value
      })
      errors.value.general = 'Validation failed'
    }
  }
}, { immediate: true })

const roles = [
  { title: t('auth.roles.customer'), value: 'customer' },
  { title: t('auth.roles.courier'), value: 'courier' },
  { title: t('auth.roles.partner'), value: 'partner' }
]

const vehicleTypes = [
  { title: t('auth.vehicles.motorcycle'), value: 'motorcycle' },
  { title: t('auth.vehicles.bicycle'), value: 'bicycle' },
  { title: t('auth.vehicles.car'), value: 'car' }
]

const businessTypes = [
  { title: t('auth.business_types.restaurant'), value: 'restaurant' },
  { title: t('auth.business_types.cafe'), value: 'cafe' },
  { title: t('auth.business_types.bakery'), value: 'bakery' },
  { title: t('auth.business_types.grocery'), value: 'grocery' }
]

const emit = defineEmits(['success', 'error'])

    async function submit() {
      try {
        const logStore = useLogStore()
        logStore.log(`🔑 Attempting ${props.mode} for email: ${form.value.email}`, 'debug')
        
        const valid = await formRef.value?.validate()
        if (!valid) {
          const errorMsg = 'Please fix the validation errors'
          errors.value = { 
            general: errorMsg,
            email: 'Email is required',
            password: 'Password is required'
          }
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
          try {
            const response = await authStore.login(credentials, router)
            
            if (response?.token) {
              logStore.log(`✅ Login successful for ${credentials.email}`, 'info')
              emit('success', { token: response.token })
              
              // Redirect based on user type
              const userType = authStore.user?.type
              const redirectPath = 
                userType === 'admin' ? '/admin/dashboard' :
                userType === 'courier' ? '/courier/dashboard' :
                userType === 'partner' ? '/partner/dashboard' :
                '/customer/dashboard'
              
              logStore.log(`🛣️ Redirecting to: ${redirectPath}`)
              router.visit(redirectPath)
              return response
            }
          } catch (error) {
            logStore.log(`❌ Login failed for ${credentials.email}: ${error.message}`, 'error')
            errors.value = { 
              general: error.response?.data?.message || 'The provided credentials are incorrect',
              email: error.response?.data?.errors?.email?.[0],
              password: error.response?.data?.errors?.password?.[0]
            }
            emit('error', error)
            return Promise.reject(error)
          }
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
