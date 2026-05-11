<template>
  <PartnerLayout>
    <div data-cy="partner-profile">
      <h1>Partner Profile</h1>
      <v-card class="mt-4">
        <v-card-text>
          <v-text-field v-model="form.name" label="Name" />
          <v-text-field v-model="form.email" label="Email" disabled />
          <v-text-field v-model="form.phone" label="Phone" @input="form.phone = maskPhone($event.target.value)" />
          
          <div class="mb-4">
            <label class="v-label mb-2 d-block">Profile Photo</label>
            <div class="d-flex align-center gap-4">
              <v-avatar size="100" class="elevation-2" rounded="lg">
                <v-img :src="photoPreview || resolveImageUrl(form.photoUrl)" cover>
                  <template v-slot:placeholder>
                    <v-icon size="large" icon="mdi-account"></v-icon>
                  </template>
                </v-img>
              </v-avatar>
              <v-file-input
                v-model="photoFile"
                label="Choose a photo"
                prepend-icon="mdi-camera"
                accept="image/*"
                @change="handlePhotoChange"
                variant="outlined"
                density="compact"
                hide-details
                class="flex-grow-1"
              />
            </div>
          </div>

          <v-divider class="my-4" />
          <v-text-field v-model="form.business_name" label="Business Name" />
          <v-select v-model="form.business_type" :items="['restaurant', 'market', 'pharmacy']" label="Business Type" />
          <v-text-field v-model="form.tax_id" label="Tax ID" @input="form.tax_id = maskCNPJ($event.target.value)" />
          <v-text-field v-model="form.address" label="Address" />

          <v-btn color="primary" @click="saveProfile" :loading="saving" class="mt-4">
            Save
          </v-btn>
        </v-card-text>
      </v-card>

      <v-alert v-if="successMessage" type="success" class="mt-4">
        {{ successMessage }}
      </v-alert>

      <v-divider class="my-6" />

      <h2>Additional Roles</h2>
      <v-card class="mt-4">
        <v-card-text>
          <p>
            <a href="/customer/dashboard">Access as Customer</a>
          </p>
          <v-divider class="my-4" />
          <div v-if="!isCourier">
            <p>Want to become a courier?</p>
            <v-btn color="primary" variant="outlined" class="mt-2" @click="showCourierForm = true">
              Become a Courier
            </v-btn>
            <v-card v-if="showCourierForm" class="mt-2 pa-4">
              <v-text-field v-model="courierForm.license_number" label="License Number" />
              <v-select v-model="courierForm.vehicle_type" :items="['motorcycle', 'car', 'bicycle']" label="Vehicle Type" />
              <v-btn color="primary" @click="addRole('courier')" :loading="addingRole">
                Submit
              </v-btn>
            </v-card>
          </div>
          <div v-else>
            <p>You are already a courier.</p>
          </div>
        </v-card-text>
      </v-card>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import api from '@/services/api'
import PartnerLayout from '@/Layouts/PartnerLayout.vue'
import { useMasks } from '@/composables/useMasks'
import { useNotificationStore } from '@/stores/notification'

const page = usePage()
const authStore = useAuthStore()
const { user } = storeToRefs(authStore)
const { maskPhone, maskCNPJ } = useMasks()
const notifications = useNotificationStore()
const saving = ref(false)
const addingRole = ref(false)
const successMessage = ref('')
const showCourierForm = ref(false)
const photoFile = ref(null)
const photoPreview = ref(null)

const resolveImageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `/storage/${path}`
}

const handlePhotoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    photoFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      photoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const form = ref({
  name: '',
  email: '',
  phone: '',
  photoUrl: '',
  business_name: '',
  business_type: '',
  tax_id: '',
  address: '',
})

const courierForm = ref({
  license_number: '',
  vehicle_type: '',
})

const allRoles = computed(() => user.value?.all_roles || [])
const isCourier = computed(() => allRoles.value.includes('courier'))

onMounted(() => {
  const userData = page.props.user
  if (userData) {
    form.value.name = userData.name || ''
    form.value.email = userData.email || ''
    form.value.phone = userData.phone || ''
    form.value.photoUrl = userData.photoUrl || ''
    form.value.business_name = userData.business_name || ''
    form.value.business_type = userData.business_type || ''
    form.value.tax_id = userData.tax_id || ''
    form.value.address = userData.address || ''
  }
})

const saveProfile = async () => {
  saving.value = true
  successMessage.value = ''
  try {
    const formData = new FormData()
    formData.append('_method', 'PATCH')
    formData.append('name', form.value.name)
    formData.append('phone', form.value.phone)
    formData.append('business_name', form.value.business_name)
    formData.append('business_type', form.value.business_type)
    formData.append('tax_id', form.value.tax_id)
    formData.append('address', form.value.address)
    
    if (photoFile.value) {
      formData.append('photo', photoFile.value)
    }

    const response = await api.post('/users/me', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    
    // Update local user state
    authStore.user = { ...authStore.user, ...response.data }
    successMessage.value = 'Profile updated successfully'
    photoFile.value = null
    photoPreview.value = null
  } catch (err) {
    notifications.error('Failed to save profile')
    console.error(err)
  } finally {
    saving.value = false
  }
}

const addRole = async (role) => {
  addingRole.value = true
  try {
    const data = { role, ...courierForm.value }
    const response = await api.post('/users/me/roles', data)
    authStore.user = response.data.user
    successMessage.value = 'Role added successfully!'
    showCourierForm.value = false
  } catch (err) {
    notifications.error('Failed to add role')
  } finally {
    addingRole.value = false
  }
}
</script>
