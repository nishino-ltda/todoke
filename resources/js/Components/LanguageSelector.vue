<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import axios from '@/services/api'

const { locale } = useI18n()
const authStore = useAuthStore()

const languages = [
  { code: 'en', name: 'English' },
  { code: 'pt-BR', name: 'Português (BR)' }
]

const displayCode = computed(() => {
  return locale.value === 'pt-BR' ? 'PT' : 'EN'
})

function toggle() {
  const currentIndex = languages.findIndex(l => l.code === locale.value)
  const nextIndex = (currentIndex + 1) % languages.length
  const nextLocale = languages[nextIndex].code
  locale.value = nextLocale
  if (authStore.isAuthenticated) {
    axios.patch('/api/v1/user/locale', { locale: nextLocale }).catch(error => {
      console.error('Failed to save language preference:', error)
    })
  }
}
</script>

<template>
  <v-badge
    :content="displayCode"
    color="primary"
    overlap
    data-cy="language-selector"
  >
    <v-btn
      icon
      variant="text"
      @click="toggle"
    >
      <v-icon>mdi-translate</v-icon>
    </v-btn>
  </v-badge>
</template>

<style scoped>
:deep(.v-badge__badge) {
  bottom: calc(100% - 24px) !important;
  left: calc(100% - 24px) !important;
}
</style>
