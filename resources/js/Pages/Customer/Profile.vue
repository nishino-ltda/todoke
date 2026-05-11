<template>
  <CustomerLayout>
    <div data-cy="customer-profile">
      <h1>Customer Profile</h1>
      <v-card class="mt-4">
        <v-card-text>
          <v-text-field 
            v-model="form.name" 
            label="Name" 
            :error-messages="form.errors.name"
            data-cy="profile-name" 
          />
          <v-text-field v-model="form.email" label="Email" disabled data-cy="profile-email" />
          
          <v-text-field 
            v-model="form.phone" 
            label="Phone" 
            :error-messages="form.errors.phone"
            data-cy="profile-phone" 
            @input="form.phone = maskPhone($event.target.value)"
          />

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
                label="Choose a photo"
                prepend-icon="mdi-camera"
                accept="image/*"
                @change="handlePhotoChange"
                variant="outlined"
                density="compact"
                hide-details
                class="flex-grow-1"
                data-cy="profile-photo-input"
              />
            </div>
          </div>

          <v-btn color="primary" @click="saveProfile" :loading="form.processing" data-cy="save-profile-btn">
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
              <v-text-field v-model="partnerForm.tax_id" label="Tax ID" @input="partnerForm.tax_id = maskCNPJ($event.target.value)" />
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
import { ref, onMounted, computed, watch } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import api from '@/services/api'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import { useMasks } from '@/composables/useMasks'

const authStore = useAuthStore()
const { user } = storeToRefs(authStore)
const { maskPhone, maskCNPJ } = useMasks()
const page = usePage()
const addingRole = ref(false)
const successMessage = ref('')
const showPartnerForm = ref(false)
const showCourierForm = ref(false)
const photoPreview = ref(null)

const form = useForm({
  name: '',
  email: '',
  phone: '',
  photoUrl: '',
  photo: null,
})

const resolveImageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `/storage/${path}`
}

const handlePhotoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.photo = file
    const reader = new FileReader()
    reader.onload = (e) => {
      photoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

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

console.log('[Profile] page.props.user:', page.props.user?.name, page.props.user?.email)

const populateForm = () => {
  const data = page.props.user
  if (data) {
    form.name = data.name || ''
    form.email = data.email || ''
    form.phone = data.phone || ''
    form.photoUrl = data.photoUrl || ''
  }
}

watch(() => page.props.user, (newUser) => {
  if (newUser && !form.processing) {
    populateForm()
  }
}, { immediate: true })

onMounted(() => {
  if (!form.name) {
    populateForm()
  }
})

const saveProfile = () => {
  successMessage.value = ''
  // Use post with _method spoofing for file upload support in Laravel PATCH routes
  form.transform((data) => ({
    ...data,
    _method: 'PATCH',
  })).post(route('customer.profile.update'), {
    onSuccess: () => {
      successMessage.value = 'Profile updated successfully'
      form.photo = null
      photoPreview.value = null
    },
    preserveScroll: true
  })
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
