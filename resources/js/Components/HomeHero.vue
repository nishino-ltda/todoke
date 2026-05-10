<template>
  <section class="home-hero d-flex align-center" data-cy="home-hero">
    <div class="hero-overlay"></div>
    <v-container class="position-relative z-1">
      <v-row>
        <v-col cols="12" md="10" lg="8">
          <v-chip
            color="primary"
            variant="flat"
            class="mb-6 font-weight-bold"
            prepend-icon="mdi-github"
            data-cy="hero-badge"
          >
            100% OPEN SOURCE & COMUNITÁRIO
          </v-chip>
          <h1 class="text-h2 font-weight-black mb-6 hero-text shadow-text" data-cy="hero-title">
            {{ t('home.hero.title') }}
          </h1>
          <p class="text-h5 hero-text font-weight-medium shadow-text mb-0" data-cy="hero-subtitle">
            {{ t('home.hero.subtitle') }}
          </p>
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
logStore.log('🏠 HomeHero updated with resistance/open-source message')
</script>

<style scoped>
.home-hero {
  position: relative;
  min-height: 550px;
  padding: 100px 0;
  background-image: url('/images/hero.png');
  background-size: cover;
  background-position: center;
  color: white;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(90deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 100%);
  z-index: 0;
}

.z-1 {
  z-index: 1;
}

.hero-text {
  color: white !important;
  line-height: 1.2;
}

.shadow-text {
  text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.9);
}

.font-weight-black {
  font-weight: 900 !important;
}
</style>
