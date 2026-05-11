<template>
  <v-form :ref="el => formRef = el" @submit.prevent="submit">
      <v-alert
        v-if="errors.general"
        type="error"
        data-cy="auth-alert"
        class="mb-4"
      >
        {{ errors.general }}
      </v-alert>

      <v-alert
        v-if="Object.keys(errors).length > 0" 
        type="error"
        variant="tonal"
        data-cy="validation-alert"
        class="mb-4"
      >
        {{ errors.general || t('auth.validation.general_error') }}
      </v-alert>

    <div class="auth-fields">
    <template v-if="mode === 'login'">
      <v-text-field
        v-model="form.email"
        :label="t('auth.form.email')"
        type="email"
        :rules="rules.email"
        required
        :error-messages="errors.email"
        prepend-inner-icon="mdi-email-outline"
        variant="outlined"
        density="comfortable"
        data-cy="email-input"
        @blur="validateField('email')"
      ></v-text-field>

      <v-text-field
        v-model="form.password"
        :label="t('auth.form.password')"
        :type="showPassword ? 'text' : 'password'"
        :rules="rules.password"
        required
        :error-messages="errors.password"
        prepend-inner-icon="mdi-lock-outline"
        :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
        variant="outlined"
        density="comfortable"
        data-cy="password-input"
        @click:append-inner="showPassword = !showPassword"
        @blur="validateField('password')"
      ></v-text-field>
    </template>

    <template v-if="mode === 'register'">
      <v-row dense>
        <v-col cols="12">
          <v-text-field
            v-model="form.name"
            :label="t('auth.form.name')"
            :rules="rules.name"
            required
            :error-messages="errors.name"
            prepend-inner-icon="mdi-account-outline"
            variant="outlined"
            density="comfortable"
            data-cy="name-input"
            @blur="validateField('name')"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6">
          <v-text-field
            v-model="form.phone"
            :label="t('auth.form.phone')"
            :rules="rules.phone"
            required
            :error-messages="errors.phone"
            prepend-inner-icon="mdi-phone-outline"
            variant="outlined"
            density="comfortable"
            placeholder="(00) 00000-0000"
            data-cy="phone-input"
            @blur="validateField('phone')"
            @input="form.phone = maskPhone($event.target.value)"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6">
          <v-text-field
            v-model="form.cpf"
            :label="t('auth.form.cpf')"
            :rules="rules.cpf"
            required
            :error-messages="errors.cpf"
            prepend-inner-icon="mdi-card-account-details-outline"
            variant="outlined"
            density="comfortable"
            placeholder="000.000.000-00"
            data-cy="cpf-input"
            @blur="validateField('cpf')"
            @input="form.cpf = maskCPF($event.target.value)"
          ></v-text-field>
        </v-col>

        <v-col cols="12">
          <v-text-field
            v-model="form.email"
            :label="t('auth.form.email')"
            type="email"
            :rules="rules.email"
            required
            :error-messages="errors.email"
            prepend-inner-icon="mdi-email-outline"
            variant="outlined"
            density="comfortable"
            data-cy="email-input"
            @blur="validateField('email')"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6">
          <v-text-field
            v-model="form.password"
            :label="t('auth.form.password')"
            :type="showPassword ? 'text' : 'password'"
            :rules="rules.password"
            required
            :error-messages="errors.password"
            prepend-inner-icon="mdi-lock-outline"
            :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
            variant="outlined"
            density="comfortable"
            data-cy="password-input"
            @click:append-inner="showPassword = !showPassword"
            @blur="validateField('password')"
          ></v-text-field>
        </v-col>

        <v-col cols="12" sm="6">
          <v-text-field
            v-model="form.password_confirmation"
            :label="t('auth.form.confirm_password')"
            :type="showPasswordConfirmation ? 'text' : 'password'"
            required
            :error-messages="errors.password_confirmation"
            prepend-inner-icon="mdi-lock-check-outline"
            :append-inner-icon="showPasswordConfirmation ? 'mdi-eye-off' : 'mdi-eye'"
            variant="outlined"
            density="comfortable"
            data-cy="password-confirmation-input"
            @click:append-inner="showPasswordConfirmation = !showPasswordConfirmation"
            @blur="validateField('password_confirmation')"
          ></v-text-field>
        </v-col>

        <v-col cols="12">
          <v-select
            v-model="form.role"
            :items="roles"
            :label="t('auth.form.account_type')"
            required
            :error-messages="errors.role"
            prepend-inner-icon="mdi-account-cog-outline"
            variant="outlined"
            density="comfortable"
            data-cy="role-select"
            @update:modelValue="handleRoleChange"
          ></v-select>
        </v-col>

        <!-- Courier specific fields -->
        <template v-if="showCourierFields">
          <v-col cols="12">
            <v-text-field
              v-model="form.license_number"
              :label="t('auth.form.license_number')"
              required
              :error-messages="errors.license_number"
              prepend-inner-icon="mdi-card-bulleted-outline"
              variant="outlined"
              density="comfortable"
              data-cy="license-input"
            ></v-text-field>
          </v-col>

          <v-col cols="12">
            <v-select
              v-model="form.vehicle_type"
              :items="vehicleTypes"
              :label="t('auth.form.vehicle_type')"
              required
              :error-messages="errors.vehicle_type"
              prepend-inner-icon="mdi-moped-outline"
              variant="outlined"
              density="comfortable"
              data-cy="vehicle-select"
            ></v-select>
          </v-col>

          <v-col cols="12">
            <v-file-input
              v-model="form.document"
              :label="t('auth.form.upload_license')"
              accept="image/*"
              required
              :error-messages="errors.document"
              prepend-inner-icon="mdi-file-document-outline"
              variant="outlined"
              density="comfortable"
              data-cy="document-upload"
            ></v-file-input>
          </v-col>
        </template>

        <!-- Partner specific fields -->
        <template v-if="showPartnerFields">
          <v-col cols="12">
            <v-text-field
              v-model="form.business_name"
              :label="t('auth.form.business_name')"
              required
              :error-messages="errors.business_name"
              prepend-inner-icon="mdi-store-outline"
              variant="outlined"
              density="comfortable"
              data-cy="business-name-input"
            ></v-text-field>
          </v-col>

          <v-col cols="12">
            <v-select
              v-model="form.business_type"
              :items="businessTypes"
              :label="t('auth.form.business_type')"
              required
              :error-messages="errors.business_type"
              prepend-inner-icon="mdi-silverware-fork-knife"
              variant="outlined"
              density="comfortable"
              data-cy="business-type-select"
            ></v-select>
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="form.tax_id"
              :label="t('auth.form.tax_id')"
              required
              :error-messages="errors.tax_id"
              prepend-inner-icon="mdi-identifier"
              variant="outlined"
              density="comfortable"
              data-cy="tax-id-input"
              @input="form.tax_id = maskCNPJ($event.target.value)"
            ></v-text-field>
          </v-col>

          <v-col cols="12">
            <v-text-field
              v-model="form.address"
              :label="t('auth.form.address')"
              required
              :error-messages="errors.address"
              prepend-inner-icon="mdi-map-marker-outline"
              variant="outlined"
              density="comfortable"
              data-cy="address-input"
            ></v-text-field>
          </v-col>

          <v-col cols="12">
            <v-file-input
              v-model="form.business_document"
              :label="t('auth.form.upload_business_doc')"
              accept=".pdf,.jpg,.png"
              required
              :error-messages="errors.business_document"
              prepend-inner-icon="mdi-file-document-outline"
              variant="outlined"
              density="comfortable"
              data-cy="business-document-upload"
            ></v-file-input>
          </v-col>
        </template>
      </v-row>
    </template>
    </div>

    <v-btn
      type="submit"
      color="primary"
      size="large"
      block
      elevation="2"
      class="mt-4"
      :disabled="loading || undefined"
      :data-cy="(mode === 'login' ? 'login-button' : 'register-button') + ' submit-btn'"
      :loading="loading"
    >
      <template v-if="!loading">
        <span data-cy="button-text">
          {{ mode === 'login' ? t('auth.login') : t('auth.register') }}
        </span>
      </template>
      <template v-else>
        <v-progress-circular
          indeterminate
          size="20"
          width="2"
          data-cy="button-loader"
        ></v-progress-circular>
      </template>
    </v-btn>
  </v-form>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'
import { useMasks } from '@/composables/useMasks'

const { t } = useI18n()
const { maskPhone, maskCPF, maskCNPJ, validateCPF, validateCNPJ } = useMasks()
const logStoreInstance = useLogStore();
logStoreInstance.log('😎 AuthForm component initialized.');

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

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

onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  const type = params.get('type')
  if (type && ['customer', 'courier', 'partner'].includes(type)) {
    form.value.role = type
    handleRoleChange(type)
  }
})

  const form = ref({
    email: '',
    password: '',
    name: '',
    phone: '',
    cpf: '',
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
    phone: [
      v => !!v || t('auth.validation.required', { field: t('auth.form.phone') }),
      v => {
        if (!v) return true
        const digits = v.replace(/\D/g, '')
        return (digits.length >= 10 && digits.length <= 11) || t('auth.validation.phone_format')
      }
    ],
    cpf: [
      v => !!v || t('auth.validation.required', { field: t('auth.form.cpf') }),
      v => validateCPF(v) || t('auth.validation.cpf_format')
    ],
    tax_id: [
      v => !!v || t('auth.validation.required', { field: t('auth.form.tax_id') }),
      v => validateCNPJ(v) || t('auth.validation.cnpj_format')
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
    if (newError.message) {
      errors.value.general = newError.message
    }
    // Handle error object with errors array
    if (newError.errors) {
      Object.entries(newError.errors).forEach(([key, value]) => {
        errors.value[key] = Array.isArray(value) ? value.join(', ') : value
      })
      if (!errors.value.general) {
        errors.value.general = t('auth.validation.general_error')
      }
    }
  }
}, { immediate: true })

const roles = [
  { 
    title: t('auth.roles.customer'), 
    value: 'customer',
    props: {
      'data-cy': 'role-customer'
    }
  },
  { 
    title: t('auth.roles.courier'), 
    value: 'courier',
    props: {
      'data-cy': 'role-courier'
    }
  },
  { 
    title: t('auth.roles.partner'), 
    value: 'partner',
    props: {
      'data-cy': 'role-partner'
    }
  }
]

const vehicleTypes = [
  { 
    title: t('auth.vehicles.motorcycle'), 
    value: 'motorcycle',
    props: {
      'data-cy': 'vehicle-motorcycle'
    }
  },
  { 
    title: t('auth.vehicles.bicycle'), 
    value: 'bicycle',
    props: {
      'data-cy': 'vehicle-bicycle'
    }
  },
  { 
    title: t('auth.vehicles.car'), 
    value: 'car',
    props: {
      'data-cy': 'vehicle-car'
    }
  }
]

const businessTypes = [
  { 
    title: t('auth.business_types.restaurant'), 
    value: 'restaurant',
    props: {
      'data-cy': 'business-restaurant'
    }
  },
  { 
    title: t('auth.business_types.cafe'), 
    value: 'cafe',
    props: {
      'data-cy': 'business-cafe'
    }
  },
  { 
    title: t('auth.business_types.bakery'), 
    value: 'bakery',
    props: {
      'data-cy': 'business-bakery'
    }
  },
  { 
    title: t('auth.business_types.grocery'), 
    value: 'grocery',
    props: {
      'data-cy': 'business-grocery'
    }
  }
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
              window.location.href = redirectPath
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
      try {
        let registerData;
        const hasFiles = form.value.document instanceof File || form.value.business_document instanceof File;
        
        // Prepare payload with all required fields
        const payload = {
          name: form.value.name,
          email: form.value.email,
          phone: form.value.phone,
          cpf: form.value.cpf,
          type: form.value.role,
          password: form.value.password,
          password_confirmation: form.value.password_confirmation,
          // Include all fields regardless of role to ensure validation passes
          license_number: form.value.license_number,
          vehicle_type: form.value.vehicle_type,
          document: form.value.document,
          business_name: form.value.business_name,
          business_type: form.value.business_type,
          tax_id: form.value.tax_id,
          address: form.value.address,
          business_document: form.value.business_document
        };

        // Add role-specific fields
        if (form.value.role === 'courier') {
          payload.license_number = form.value.license_number;
          payload.vehicle_type = form.value.vehicle_type;
          payload.license_file = form.value.document;
        } else if (form.value.role === 'partner') {
          payload.business_name = form.value.business_name;
          payload.business_type = form.value.business_type;
          payload.tax_id = form.value.tax_id;
          payload.address = form.value.address;
          payload.business_document = form.value.business_document;
        }

        if (hasFiles) {
          const formData = new FormData();
          Object.entries(payload).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
              if (value instanceof File) {
                formData.append(key, value);
              } else {
                formData.append(key, value.toString());
              }
            }
          });
          registerData = formData;
        } else {
          registerData = payload;
        }

        logStore.log(`📝 Sending registration request for ${form.value.email}`, 'debug')
        const response = await authStore.register(registerData, router)
        if (response?.data?.token) {
          logStore.log(`✅ Registration successful for ${form.value.email}`, 'info')
          emit('success', { token: response.data.token })

          logStore.log(`✅ User registered successfully, redirecting to dashboard`, 'info')
          await router.visit('/customer/dashboard')
          return response
        }
        throw new Error('Registration failed - no token received')
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
  } catch (error) {
    const logStore = useLogStore()
    logStore.log(`❌ ${props.mode} failed: ${error.message}`, 'error')
    errors.value.general = error.message || 'An unexpected error occurred'
    emit('error', error)
    return Promise.reject(error)
  }
}
</script>
