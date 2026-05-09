<template>
  <AuthenticatedLayout>
    <v-row>
      <v-col cols="12" md="3">
        <v-card border elevation="0" class="mb-4">
          <v-list>
            <v-list-item
              v-for="item in supportNav"
              :key="item.to"
              :title="t(item.title)"
              :prepend-icon="item.icon"
              link
              @click="router.visit(item.to)"
              :active="isLinkActive(item.to)"
              data-cy="support-nav-item"
            />
          </v-list>
        </v-card>
      </v-col>
      <v-col cols="12" md="9">
        <slot />
      </v-col>
    </v-row>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const { t } = useI18n();
const page = usePage();

const isLinkActive = (path) => {
  return page.url.startsWith(path);
};

const supportNav = [
  { title: 'support.dashboard.title', to: '/support', icon: 'mdi-view-dashboard-outline' },
  { title: 'support.tickets.title', to: '/support/tickets', icon: 'mdi-ticket-outline' },
  { title: 'support.faq.title', to: '/support/faq', icon: 'mdi-help-circle-outline' }
];
</script>
