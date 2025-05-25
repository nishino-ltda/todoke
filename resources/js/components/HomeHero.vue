<template>
  <section class="home-hero" data-test="home-hero">
    <v-container>
      <v-row>
        <v-col cols="12" md="6" class="d-flex flex-column justify-center">
          <h1 class="text-h3 font-weight-bold mb-4" data-test="hero-title">
            {{ t('home.hero.title') }}
          </h1>
          <p class="text-h6 mb-6" data-test="hero-subtitle">
            {{ t('home.hero.subtitle') }}
          </p>
          <div class="d-flex gap-4">
            <v-btn
              color="primary"
              size="x-large"
              :to="route('register')"
              data-test="hero-register-btn"
            >
              {{ t('home.hero.register_button') }}
            </v-btn>
            <v-btn
              variant="outlined"
              color="primary"
              size="x-large"
              :to="route('login')"
              data-test="hero-login-btn"
            >
              {{ t('home.hero.login_button') }}
            </v-btn>
          </div>
        </v-col>
        <v-col cols="12" md="6">
          <v-img
            src="/images/hero-image.png"
            alt="Hybrid delivery illustration"
            data-test="hero-image"
          />
        </v-col>
      </v-row>
    </v-container>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { route } from '@inertiajs/vue3'
import { useLogStore } from '@/stores/log'

const { t } = useI18n()
const logStore = useLogStore()
logStore.log('🏠 HomeHero component initialized')
</script>

<style scoped>
.home-hero {
  padding: 80px 0;
  background: linear-gradient(to right, #f5f7fa, #e4e8f0);
}

.gap-4 {
  gap: 16px;
}
</style>
