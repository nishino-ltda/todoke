<template>
  <AdminLayout>
    <div data-cy="admin-profile">
      <h1>Admin Profile</h1>
      <v-card class="mt-4">
        <v-card-text>
          <v-text-field v-model="form.name" label="Name" />
          <v-text-field v-model="form.email" label="Email" disabled />
          <v-text-field v-model="form.phone" label="Phone" />
          
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

          <v-btn color="primary" @click="saveProfile" :loading="saving" class="mt-4">
            Save
          </v-btn>
        </v-card-text>
      </v-card>

      <v-alert v-if="successMessage" type="success" class="mt-4">
        {{ successMessage }}
      </v-alert>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import api from '@/services/api'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useNotificationStore } from '@/stores/notification'

const page = usePage()
const authStore = useAuthStore()
const { user } = storeToRefs(authStore)
const notifications = useNotificationStore()
const saving = ref(false)
const successMessage = ref('')
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
})

onMounted(() => {
  const userData = page.props.user
  if (userData) {
    form.value.name = userData.name || ''
    form.value.email = userData.email || ''
    form.value.phone = userData.phone || ''
    form.value.photoUrl = userData.photoUrl || ''
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
</script>
