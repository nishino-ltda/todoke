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
  <v-btn
    variant="text"
    density="compact"
    class="language-badge"
    data-cy="language-selector"
    @click="toggle"
  >
    <span class="language-code">{{ displayCode }}</span>
  </v-btn>
</template>

<style scoped>
.language-badge {
  min-width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid currentColor;
  padding: 0;
}
.language-code {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
}
</style>
