<script setup>
import { useI18n } from 'vue-i18n'
const { locale } = useI18n()

const languages = [
  { code: 'en', name: 'English' },
  { code: 'pt-BR', name: 'Português (BR)' }
]
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
