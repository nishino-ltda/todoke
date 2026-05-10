<template>
  <CourierLayout>
    <div data-cy="courier-profile">
      <h1>Courier Profile</h1>
      <v-card class="mt-4">
        <v-card-text>
          <v-text-field v-model="form.name" label="Name" />
          <v-text-field v-model="form.email" label="Email" disabled />
          <v-text-field v-model="form.phone" label="Phone" />
          <v-text-field v-model="form.photoUrl" label="Photo URL" />
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
              <v-text-field v-model="partnerForm.tax_id" label="Tax ID" />
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
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import api from '@/services/api'
import CourierLayout from '@/Layouts/CourierLayout.vue'
import { useNotificationStore } from '@/stores/notification'

const authStore = useAuthStore()
const { user } = storeToRefs(authStore)
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
  if (user.value) {
    form.value.name = user.value.name || ''
    form.value.email = user.value.email || ''
    form.value.phone = user.value.phone || ''
    form.value.photoUrl = user.value.photoUrl || ''
    form.value.license_number = user.value.license_number || ''
    form.value.vehicle_type = user.value.vehicle_type || ''
  }
})

const saveProfile = async () => {
  saving.value = true
  successMessage.value = ''
  try {
    const payload = {
      name: form.value.name,
      phone: form.value.phone,
      photoUrl: form.value.photoUrl,
      license_number: form.value.license_number,
      vehicle_type: form.value.vehicle_type,
    }
    await api.patch('/users/me', payload)
    Object.assign(authStore.user, payload)
    successMessage.value = 'Profile updated successfully'
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
