<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const props = defineProps({
  minimal: {
    type: Boolean,
    default: false
  }
})
</script>

<template>
  <v-footer app color="secondary" dark class="app-footer" data-cy="app-footer">
    <div class="d-flex align-center w-100" v-if="!minimal">
      <div>
        <Link :href="route('terms')" class="text-white mr-2" data-cy="terms-link">{{ t('footer.terms') }}</Link>
        <Link :href="route('privacy')" class="text-white" data-cy="privacy-link">{{ t('footer.privacy') }}</Link>
      </div>
      <div class="flex-grow-1"></div>
      <div>
        <v-btn variant="text" color="white" href="https://github.com/nishino-ltda" target="_blank" data-cy="github-link">
          <v-icon>mdi-github</v-icon>
        </v-btn>
      </div>
    </div>
  </v-footer>
</template>

<style scoped>
.app-footer {
  font-size: 0.75rem;
  padding: 0px 16px;
  opacity: 0.85;
}
</style>

