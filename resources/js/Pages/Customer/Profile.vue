<template>
  <CustomerLayout>
    <div data-cy="customer-profile">
      <h1>Customer Profile</h1>
      <v-card class="mt-4">
        <v-card-text>
          <v-text-field v-model="form.name" label="Name" data-cy="profile-name" />
          <v-text-field v-model="form.email" label="Email" disabled data-cy="profile-email" />
          <v-text-field v-model="form.phone" label="Phone" data-cy="profile-phone" />
          <v-text-field v-model="form.photoUrl" label="Photo URL" data-cy="profile-photo" />

          <v-btn color="primary" @click="saveProfile" :loading="saving" data-cy="save-profile-btn">
            Save
          </v-btn>
        </v-card-text>
      </v-card>

      <v-alert v-if="successMessage" type="success" class="mt-4" data-cy="profile-success">
        {{ successMessage }}
      </v-alert>

      <v-divider class="my-6" />

      <h2>Additional Roles</h2>
      <v-card class="mt-4">
        <v-card-text>
          <div v-if="!isPartner" class="mb-4">
            <p>Want to become a partner?</p>
            <v-btn color="primary" variant="outlined" @click="showPartnerForm = true" data-cy="become-partner-btn">
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
            <p>You are already a partner. <a href="/partner/dashboard">Go to Partner Dashboard</a></p>
          </div>

          <v-divider class="my-4" />

          <div v-if="!isCourier" class="mb-4">
            <p>Want to become a courier?</p>
            <v-btn color="primary" variant="outlined" @click="showCourierForm = true" data-cy="become-courier-btn">
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
            <p>You are already a courier. <a href="/courier/dashboard">Go to Courier Dashboard</a></p>
          </div>
        </v-card-text>
      </v-card>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import api from '@/services/api'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'

const authStore = useAuthStore()
const { user } = storeToRefs(authStore)
const saving = ref(false)
const addingRole = ref(false)
const successMessage = ref('')
const showPartnerForm = ref(false)
const showCourierForm = ref(false)

const form = ref({
  name: '',
  email: '',
  phone: '',
  photoUrl: '',
})

const partnerForm = ref({
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
const isPartner = computed(() => allRoles.value.includes('partner'))
const isCourier = computed(() => allRoles.value.includes('courier'))

onMounted(() => {
  if (user.value) {
    form.value.name = user.value.name || ''
    form.value.email = user.value.email || ''
    form.value.phone = user.value.phone || ''
    form.value.photoUrl = user.value.photoUrl || ''
  }
})

const saveProfile = async () => {
  saving.value = true
  successMessage.value = ''
  try {
    await api.patch('/users/me', {
      name: form.value.name,
      phone: form.value.phone,
      photoUrl: form.value.photoUrl,
    })
    authStore.user.name = form.value.name
    authStore.user.phone = form.value.phone
    authStore.user.photoUrl = form.value.photoUrl
    successMessage.value = 'Profile updated successfully'
  } catch (err) {
    console.error('Failed to save profile', err)
  } finally {
    saving.value = false
  }
}

const addRole = async (role) => {
  addingRole.value = true
  try {
    const data = { role }
    if (role === 'partner') {
      Object.assign(data, partnerForm.value)
    } else if (role === 'courier') {
      Object.assign(data, courierForm.value)
    }
    const response = await api.post('/users/me/roles', data)
    authStore.user = response.data.user
    successMessage.value = 'Role added successfully!'
    showPartnerForm.value = false
    showCourierForm.value = false
  } catch (err) {
    console.error('Failed to add role', err)
  } finally {
    addingRole.value = false
  }
}
</script>
