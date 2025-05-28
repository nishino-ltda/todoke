<script setup>
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { watch } from 'vue'
import axios from '@/services/api'

const { locale } = useI18n()
const authStore = useAuthStore()

const languages = [
  { code: 'en', name: 'English' },
  { code: 'pt-BR', name: 'Português (BR)' }
]

// Save language preference when changed
watch(locale, async (newLocale) => {
  if (authStore.isAuthenticated) {
    try {
      await axios.patch('/api/v1/user/locale', { locale: newLocale })
    } catch (error) {
      console.error('Failed to save language preference:', error)
    }
  }
})
</script>

<template>
  <v-select
    v-model="locale"
    :items="languages"
    item-title="name"
    item-value="code"
    density="compact"
    variant="outlined"
    hide-details
    class="language-selector"
  />
</template>

<style scoped>
.language-selector {
  max-width: 150px;
}
</style>
