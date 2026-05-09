<template>
  <section class="home-cta py-12" data-test="home-cta">
    <v-container>
      <v-row>
        <v-col cols="12">
          <h2 class="text-h4 text-center mb-4" data-test="cta-title">
            {{ t('home.cta.title') }}
          </h2>
          <p class="text-body-1 text-center mb-8" data-test="cta-description">
            {{ t('home.cta.description') }}
          </p>
        </v-col>
      </v-row>
      <v-row>
        <v-col v-for="cta in ctaItems" :key="cta.key" cols="12" md="4" :data-test="`cta-${cta.key}`">
          <v-card height="100%" class="text-center pa-4 d-flex flex-column">
            <div class="flex-grow-1">
              <v-icon size="x-large" color="primary" class="mb-4">{{ cta.icon }}</v-icon>
              <v-card-title class="text-h5 font-weight-bold">{{ t(`home.cta.${cta.key}.title`) }}</v-card-title>
              <v-card-text class="text-body-1">{{ t(`home.cta.${cta.key}.description`) }}</v-card-text>
            </div>
            <v-card-actions class="justify-center pb-4">
              <v-btn 
                color="primary" 
                variant="elevated" 
                :href="cta.route" 
                class="px-8 font-weight-bold"
                :data-test="`cta-${cta.key}-btn`"
              >
                {{ t(`home.cta.${cta.key}.button`) }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useLogStore } from '@/stores/log'

const { t } = useI18n()
const logStore = useLogStore()
logStore.log('HomeCTA component initialized')

const ctaItems = [
  { key: 'customer', icon: 'mdi-account', route: '/register?type=customer' },
  { key: 'partner', icon: 'mdi-store', route: '/register?type=partner' },
  { key: 'courier', icon: 'mdi-motorbike', route: '/register?type=courier' }
]
</script>

<style scoped>
.home-cta {
  background-color: rgb(var(--v-theme-surface-variant), 0.05);
}

.v-card {
  transition: transform 0.3s ease;
  border-radius: 16px;
}

.v-card:hover {
  transform: translateY(-8px);
}
</style>
