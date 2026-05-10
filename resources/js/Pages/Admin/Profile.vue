<template>
  <AdminLayout>
    <div data-cy="admin-profile">
      <h1>Admin Profile</h1>
      <v-card class="mt-4">
        <v-card-text>
          <v-text-field v-model="form.name" label="Name" />
          <v-text-field v-model="form.email" label="Email" disabled />
          <v-text-field v-model="form.phone" label="Phone" />
          <v-text-field v-model="form.photoUrl" label="Photo URL" />

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
import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import api from '@/services/api'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useNotificationStore } from '@/stores/notification'

const authStore = useAuthStore()
const { user } = storeToRefs(authStore)
const notifications = useNotificationStore()
const saving = ref(false)
const successMessage = ref('')

const form = ref({
  name: '',
  email: '',
  phone: '',
  photoUrl: '',
})

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
    const payload = {
      name: form.value.name,
      phone: form.value.phone,
      photoUrl: form.value.photoUrl,
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
</script>
