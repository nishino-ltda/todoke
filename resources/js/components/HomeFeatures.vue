<template>
  <v-container class="my-12" data-test="home-features">
    <v-row>
      <v-col cols="12">
        <h2 class="text-h4 text-center mb-8" data-test="features-title">
          {{ t('home.features.title') }}
        </h2>
      </v-col>
    </v-row>
    
    <v-row>
      <v-col cols="12" md="4" data-test="hybrid-feature">
        <v-card height="100%">
          <v-card-item>
            <v-icon size="x-large" color="primary" class="mb-4" data-test="hybrid-icon">mdi-motorbike</v-icon>
            <v-card-title data-test="hybrid-title">{{ t('home.features.hybrid.title') }}</v-card-title>
            <v-card-subtitle data-test="hybrid-subtitle">{{ t('home.features.hybrid.subtitle') }}</v-card-subtitle>
          </v-card-item>
          <v-card-text data-test="hybrid-description">
            {{ t('home.features.hybrid.description') }}
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4" data-test="pricing-feature">
        <v-card height="100%">
          <v-card-item>
            <v-icon size="x-large" color="primary" class="mb-4" data-test="pricing-icon">mdi-account-group</v-icon>
            <v-card-title data-test="pricing-title">{{ t('home.features.pricing.title') }}</v-card-title>
            <v-card-subtitle data-test="pricing-subtitle">{{ t('home.features.pricing.subtitle') }}</v-card-subtitle>
          </v-card-item>
          <v-card-text data-test="pricing-description">
            {{ t('home.features.pricing.description') }}
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="4" data-test="reliable-feature">
        <v-card height="100%">
          <v-card-item>
            <v-icon size="x-large" color="primary" class="mb-4" data-test="reliable-icon">mdi-shield-check</v-icon>
            <v-card-title data-test="reliable-title">{{ t('home.features.reliable.title') }}</v-card-title>
            <v-card-subtitle data-test="reliable-subtitle">{{ t('home.features.reliable.subtitle') }}</v-card-subtitle>
          </v-card-item>
          <v-card-text data-test="reliable-description">
            {{ t('home.features.reliable.description') }}
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useLogStore } from '@/stores/log'

const { t } = useI18n()
const logStore = useLogStore()
logStore.log('✨ HomeFeatures component initialized')
</script>

<style scoped>
.v-card {
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.v-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.v-card-item {
  min-height: 120px;
}
</style>
