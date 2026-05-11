<template>
  <CourierLayout>
    <div data-cy="courier-profile">
      <h1>Courier Profile</h1>
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
          <v-text-field v-model="form.license_number" label="License Number" />
          <v-select v-model="form.vehicle_type" :items="['motorcycle', 'car', 'bicycle']" label="Vehicle Type" />

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
          <div v-if="!isPartner">
            <p>Want to become a partner?</p>
            <v-btn color="primary" variant="outlined" class="mt-2" @click="showPartnerForm = true">
              Become a Partner
            </v-btn>
            <v-card v-if="showPartnerForm" class="mt-2 pa-4">
              <v-text-field v-model="partnerForm.business_name" label="Business Name" />
              <v-select v-model="partnerForm.business_type" :items="['restaurant', 'market', 'pharmacy']" label="Business Type" />
              <v-text-field v-model="partnerForm.tax_id" label="Tax ID" @input="partnerForm.tax_id = maskCNPJ($event.target.value)" />
              <v-text-field v-model="partnerForm.address" label="Address" />
              <v-btn color="primary" @click="addRole('partner')" :loading="addingRole">
                Submit
              </v-btn>
            </v-card>
          </div>
          <div v-else>
            <p>You are already a partner.</p>
          </div>
        </v-card-text>
      </v-card>
    </div>
  </CourierLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import api from '@/services/api'
import CourierLayout from '@/Layouts/CourierLayout.vue'
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
const showPartnerForm = ref(false)

const form = ref({
  name: '',
  email: '',
  phone: '',
  photoUrl: '',
  license_number: '',
  vehicle_type: '',
})

const partnerForm = ref({
  business_name: '',
  business_type: '',
  tax_id: '',
  address: '',
})

const allRoles = computed(() => user.value?.all_roles || [])
const isPartner = computed(() => allRoles.value.includes('partner'))

onMounted(() => {
  const userData = page.props.user
  if (userData) {
    form.value.name = userData.name || ''
    form.value.email = userData.email || ''
    form.value.phone = userData.phone || ''
    form.value.photoUrl = userData.photoUrl || ''
    form.value.license_number = userData.license_number || ''
    form.value.vehicle_type = userData.vehicle_type || ''
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
    formData.append('license_number', form.value.license_number)
    formData.append('vehicle_type', form.value.vehicle_type)
    
    if (photoFile.value) {
      formData.append('photo', photoFile.value)
    }

    const response = await api.post('/users/me', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    
    authStore.user = { ...authStore.user, ...response.data }
    successMessage.value = 'Profile updated successfully'
    photoFile.value = null
    photoPreview.value = null
  } catch (err) {
    notifications.error('Failed to save profile')
  } finally {
    saving.value = false
  }
}

const addRole = async (role) => {
  addingRole.value = true
  try {
    const data = { role, ...partnerForm.value }
    const response = await api.post('/users/me/roles', data)
    authStore.user = response.data.user
    successMessage.value = 'Role added successfully!'
    showPartnerForm.value = false
  } catch (err) {
    notifications.error('Failed to add role')
  } finally {
    addingRole.value = false
  }
}
</script>
